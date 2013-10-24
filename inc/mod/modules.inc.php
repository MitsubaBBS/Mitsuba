<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("modules.view");
		$search = "";
		$replace = "";
		
		if ((!empty($_GET['cfg'])) && ($_GET['cfg'] == 1) && (!empty($_GET['n'])))
		{
			$mitsuba->admin->reqPermission("modules.config");

			$result = $conn->query("SELECT * FROM modules WHERE namespace='".$conn->real_escape_string($_GET['n'])."';");
			if ($result->num_rows != 1)
			{
				echo "<b style='color: red;'>".$lang['mod/module_not_installed']."</b>";
			} else {
				$dir = "./modules/".$_GET['n'];
				include($dir."/install.php");
				$installer = new $json->install_class($conn, $mitsuba);
				$installer->uninstall();
				exit;
			}
		}

		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "upload"))
		{
			$mitsuba->admin->reqPermission("modules.upload");
			$mitsuba->admin->ui->checkToken($_POST['token']);
			if (empty($_FILES['upfile']['tmp_name']))
			{
				echo "<b style='color: red;'>".$lang['mod/no_file']."</b>";
			} else {
				$filename = strtolower(preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $_FILES['upfile']['name']));
				//Zip file checks
				$tmpname = $_FILES['upfile']['tmp_name'];
				$zip = new ZipArchive();
				if ($zip->open($tmpname) !== TRUE) {
					echo "<b style='color: red;'>".$lang['mod/no_file']."</b>";
				} elseif (is_null($zip->locateName("module.json")))
				{
					echo "<b style='color: red;'>".$lang['mod/invalid_module']."</b>";	
				} elseif ((is_null($zip->locateName("install.php"))) || (is_null($zip->locateName("config.php")))) //Does the archive contain install.php and config.php?
				{
					echo "<b style='color: red;'>".$lang['mod/invalid_module']."</b>";	
				} else {
					//Is the JSON valid?
					$json = json_decode($zip->getFromIndex($zip->locateName("module.json")));
					if (is_null($json))
					{
						echo "<b style='color: red;'>".$lang['mod/invalid_module']."</b>";	
					} elseif ((empty($json->name)) || (empty($json->namespace)) || (empty($json->description)) || (empty($json->author)) || (empty($json->version)) || (empty($json->install_class)) || (empty($json->config_class)))
					{
						echo "<b style='color: red;'>".$lang['mod/invalid_module']."</b>";	
					} elseif (is_dir("./modules/".$json->namespace."/")) {
						echo "<b style='color: red;'>".sprintf($lang['mod/module_dir_exists'], $json->namespace)."</b>";	
					} else {
						mkdir("./modules/".$json->namespace);
						$zip->extractTo("./modules/".$json->namespace."/");
						echo "<b style='color: green;'>".$lang['mod/module_uploaded']."</b>";
					}
				}
				$zip->close();
			}
		}
		
		
		if ((!empty($_GET['ins'])) && ($_GET['ins'] == 1) && (!empty($_GET['n'])))
		{
			$mitsuba->admin->reqPermission("modules.install");
			$result = $conn->query("SELECT * FROM modules WHERE namespace='".$conn->real_escape_string($_GET['n'])."';");
			if ($result->num_rows == 1)
			{
				echo "<b style='color: red;'>".$lang['mod/module_already_installed']."</b>";
			} else {
				$dir = "./modules/".$_GET['n'];
				//Validate JSON
				if ((is_dir($dir)) &&(file_exists($dir."/module.json")) && (file_exists($dir."/install.php")) && (file_exists($dir."/config.php")))
				{
					$json = json_decode(file_get_contents($dir."/module.json"));
					if ((!is_null($json)) && (!empty($json->name)) && (!empty($json->namespace)) && (!empty($json->description)) && (!empty($json->author)) && (!empty($json->version)) && (!empty($json->install_class)) && (!empty($json->config_class)))
					{
						//We can haz module!
						$continue = 1;

						//Check post fields
						if ($continue)
						{
							if ((!empty($json->new_post_fields)) && (is_array($json->new_post_fields)))
							{
								$all_fields = $conn->query("SHOW COLUMNS FROM posts;");
								$fields = array();
								while ($row = $all_fields->fetch_assoc())
								{
									$fields[$row['Field']] = 1;
								}
								foreach ($json->new_post_fields as $field) {
									if (!empty($fields[$field->name]))
									{
										//We haz a conflicting field o_O
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].sprintf($lang['mod/module_post_field_conflict'], $field->name)."</b>";
										$continue = 0;
										break;
									}
								}
							}
						}
						//Check permissions
						if ($continue)
						{
							if ((!empty($json->new_permissions)) && (is_array($json->new_permissions)))
							{
								$all_permissions = $conn->query("SELECT * FROM permissions");
								$permissions = array();
								while ($row = $all_permissions->fetch_assoc())
								{
									$permissions[$row['name']] = 1;
								}
								foreach ($json->new_permissions as $permission) {
									if (!empty($permissions["modules.".$json->namespace.".".$permission->name]))
									{
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].sprintf($lang['mod/module_permission_conflict'], "modules.".$json->namespace.".".$permission->name)."</b>";
										$continue = 0;
										break;
									}
								}
							}
						}
						//Check config
						if ($continue)
						{
							if ((!empty($json->new_config)) && (is_array($json->new_config)))
							{
								$all_config = $conn->query("SELECT * FROM module_config WHERE namespace='".$conn->real_escape_string($json->namespace)."'");
								$econfig = array();
								while ($row = $all_config->fetch_assoc())
								{
									$econfig[$row['name']] = 1;
								}
								foreach ($json->new_config as $config) {
									if (!empty($econfig[$config->name]))
									{
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].sprintf($lang['mod/module_config_conflict'], $config->name)."</b>";
										$continue = 0;
										break;
									}
								}
							}
						}
						//Check board config
						if ($continue)
						{
							if ((!empty($json->new_boardconfig)) && (is_array($json->new_boardconfig)))
							{
								$all_fields = $conn->query("SHOW COLUMNS FROM boards;");
								$fields = array();
								while ($row = $all_fields->fetch_assoc())
								{
									$fields[$row['Field']] = 1;
								}
								foreach ($json->new_boardconfig as $field) {
									if (!empty($fields[$field->name]))
									{
										//We haz a conflicting field o_O
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].sprintf($lang['mod/module_board_field_conflict'], $field->name)."</b>";
										$continue = 0;
										break;
									}
								}
							}
						}
						//Check new classes
						if ($continue)
						{
							if ((!empty($json->new_classes)) && (is_array($json->new_classes)))
							{
								foreach ($json->new_classes as $field) {
									$name = $field->name;
									if (!empty($mitsuba->$name))
									{
										//We haz a conflicting field o_O
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].sprintf($lang['mod/module_class_conflict'], $field->name)."</b>";
										$continue = 0;
										break;
									}
								}
							}
						}
						//Check extra tables
						if ($continue)
						{
							if ((!empty($json->new_tables)) && (is_array($json->new_tables)))
							{
								$all_fields = $conn->query("SHOW TABLES;");
								$fields = array();
								while ($row = $all_fields->fetch_row())
								{
									$fields[$row[0]] = 1;
								}
								foreach ($json->new_tables as $table) {
									if (!empty($fields[$table]))
									{
										//We haz a conflicting field o_O
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].sprintf($lang['mod/module_table_conflict'], $table)."</b>";
										$continue = 0;
										break;
									}
								}
							}
						}
						//Check panel pages
						if ($continue)
						{
							if ((!empty($json->panel_pages)) && (is_array($json->panel_pages)))
							{
								$all_fields = $conn->query("SELECT * FROM module_pages;");
								$fields = array();
								while ($row = $all_fields->fetch_assoc())
								{
									$fields[$row['url']] = 1;
								}
								foreach ($json->panel_pages as $pages) {
									if (!empty($fields[$pages->url]))
									{
										//We haz a conflicting field o_O
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].sprintf($lang['mod/module_page_conflict'], $field->page)."</b>";
										$continue = 0;
										break;
									}
								}
							}
						}

						$continue = 1;
						$undo_queries = array();
						//Create post fields
						if ($continue)
						{
							if ((!empty($json->new_post_fields)) && (is_array($json->new_post_fields)))
							{
								foreach ($json->new_post_fields as $field) {
									if (!$conn->query("ALTER TABLE posts ADD `".$conn->real_escape_string($field->name)."` ".$conn->real_escape_string($field->definition)))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "ALTER TABLE posts DROP `".$conn->real_escape_string($field->name)."`;";
										if (!$conn->query("INSERT INTO module_fields (`namespace`, `name`, `type`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($field->name)."', 'postfield');"))
										{
											//Oh crap we haz an error!
											$continue = 0;
											echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
											foreach ($undo_queries as $query) {
												$conn->query($query);
											}
											break;
										} else {
											$undo_queries[] = "DELETE FROM module_fields WHERE namespace='".$conn->real_escape_string($json->namespace)."' AND name='".$conn->real_escape_string($field->name)."';";
										}
									}

								}
							}
						}
						//Create permissions
						if ($continue)
						{
							if ((!empty($json->new_permissions)) && (is_array($json->new_permissions)))
							{
								$id = $conn->query("SELECT * FROM permissions_categories WHERE name='module';")->fetch_assoc()['id'];
								foreach ($json->new_permissions as $permission) {
									if (!$conn->query("INSERT INTO `permissions` (`name`, `description`, `category`) VALUES ('".$conn->real_escape_string($permission->name)."', '".$conn->real_escape_string($permission->description)."', ".$id.")"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "DELETE FROM `permissions` WHERE name='".$conn->real_escape_string($permission->name)."';";
									}

								}
							}
						}
						//Create config
						if ($continue)
						{
							if ((!empty($json->new_config)) && (is_array($json->new_config)))
							{
								foreach ($json->new_config as $config) {
									if (!$conn->query("INSERT INTO `module_config` (`namespace`, `name`, `description`, `default_value`, `value`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($config->name)."', '".$conn->real_escape_string($config->description)."', '".$conn->real_escape_string($config->default_value)."', '".$conn->real_escape_string($config->default_value)."')"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "DELETE FROM `module_config` WHERE name='".$conn->real_escape_string($config->name)."' AND namespace='".$conn->real_escape_string($json->namespace)."';";
									}

								}
							}
						}
						//Create board config
						if ($continue)
						{
							if ((!empty($json->new_boardconfig)) && (is_array($json->new_boardconfig)))
							{
								foreach ($json->new_boardconfig as $config) {
									if (!$conn->query("INSERT INTO `module_boardconfig` (`namespace`, `name`, `description`, `default_value`, `value`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($config->name)."', '".$conn->real_escape_string($config->description)."', '".$conn->real_escape_string($config->default_value)."', '".$conn->real_escape_string($config->default_value)."')"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "DELETE FROM `module_boardconfig` WHERE name='".$conn->real_escape_string($config->name)."' AND namespace='".$conn->real_escape_string($json->namespace)."';";
										if (!$conn->query("ALTER TABLE boards ADD `".$conn->real_escape_string($config->name)."` ".$conn->real_escape_string($config->definition)))
										{
											//Oh crap we haz an error!
											$continue = 0;
											echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
											foreach ($undo_queries as $query) {
												$conn->query($query);
											}
											break;
										} else {
											$undo_queries[] = "ALTER TABLE posts DROP `".$conn->real_escape_string($config->name)."`;";
										}
									}

								}
							}
						}
						//Create new classes
						if ($continue)
						{
							if ((!empty($json->new_classes)) && (is_array($json->new_classes)))
							{
								foreach ($json->new_classes as $class) {
									if (!$conn->query("INSERT INTO `module_classes` (`namespace`, `name`, `file`, `class`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($class->name)."', '".$conn->real_escape_string($class->file)."', '".$conn->real_escape_string($class->class)."')"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "DELETE FROM `module_classes` WHERE name='".$conn->real_escape_string($class->name)."';";
									}
								}
							}
						}
						//Create panel pages
						if ($continue)
						{
							if ((!empty($json->panel_pages)) && (is_array($json->panel_pages)))
							{
								foreach ($json->panel_pages as $page) {
									if (!$conn->query("INSERT INTO `module_pages` (`namespace`, `url`, `file`, `class`, `method`) VALUES ('".$conn->real_escape_string($page->namespace)."', '".$conn->real_escape_string($page->url)."', '".$conn->real_escape_string($page->file)."', '".$conn->real_escape_string($page->class)."', '".$conn->real_escape_string($page->method)."')"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "DELETE FROM `module_pages` WHERE url='".$conn->real_escape_string($page->url)."' AND namespace='".$conn->real_escape_string($json->namespace)."';";
									}
								}
							}
						}
						//Create events
						if ($continue)
						{
							if ((!empty($json->events)) && (is_array($json->events)))
							{
								foreach ($json->events as $event) {
									if (!$conn->query("INSERT INTO `module_events` (`namespace`, `event`, `file`, `class`, `method`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($event->event)."', '".$conn->real_escape_string($event->file)."', '".$conn->real_escape_string($event->class)."', '".$conn->real_escape_string($event->method)."')"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "DELETE FROM `module_events` WHERE event='".$conn->real_escape_string($event->event)."' AND namespace='".$conn->real_escape_string($json->namespace)."';";
									}
								}
							}
						}

						//Add to modules table
						if ($continue)
						{
							if (!$conn->query("INSERT INTO `modules` (`namespace`, `name`, `description`, `author`, `version`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($json->name)."', '".$conn->real_escape_string($json->description)."', '".$conn->real_escape_string($json->author)."', '".$conn->real_escape_string($json->version)."');"))
							{
								//Oh crap we haz an error!
								$continue = 0;
								echo "<b style='color:red;'>".$lang['mod/module_install_error'].$conn->error."</b>";
								foreach ($undo_queries as $query) {
									$conn->query($query);
								}
								break;
							} else {
								$undo_queries[] = "DELETE FROM `modules` WHERE namespace='".$conn->real_escape_string($json->namespace)."';";
							}
						}


						//Execute install.php (create extra tables etc.)
						if ($continue)
						{
							include($dir."/install.php");
							$installer = new $json->install_class($conn, $mitsuba);
							$installer->install();
							echo "<b style='color: green;'>".$lang['mod/module_installed']."</b>";
						}
					} else {
						echo "<b style='color: red;'>".$lang['mod/module_invalid_json']."</b>";
					}
				} else {
					echo "<b style='color: red;'>".$lang['mod/module_no_file']."</b>";
				}
			}
		}

		if ((!empty($_GET['unins'])) && ($_GET['unins'] == 1) && (!empty($_GET['n'])))
		{
			$mitsuba->admin->reqPermission("modules.uninstall");
			$result = $conn->query("SELECT * FROM modules WHERE namespace='".$conn->real_escape_string($_GET['n'])."';");
			if ($result->num_rows != 1)
			{
				echo "<b style='color: red;'>".$lang['mod/module_not_installed']."</b>";
			} else {
				$dir = "./modules/".$_GET['n'];
				//Validate JSON
				if ((is_dir($dir)) &&(file_exists($dir."/module.json")) && (file_exists($dir."/install.php")) && (file_exists($dir."/config.php")))
				{
					$json = json_decode(file_get_contents($dir."/module.json"));
					if ((!is_null($json)) && (!empty($json->name)) && (!empty($json->namespace)) && (!empty($json->description)) && (!empty($json->author)) && (!empty($json->version)) && (!empty($json->install_class)) && (!empty($json->config_class)))
					{
						$continue = 1;
						$undo_queries = array();
						//Create post fields
						if ($continue)
						{
							if ((!empty($json->new_post_fields)) && (is_array($json->new_post_fields)))
							{
								foreach ($json->new_post_fields as $field) {
									if (!$conn->query("ALTER TABLE posts DROP `".$conn->real_escape_string($field->name)."`;"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "ALTER TABLE posts ADD `".$conn->real_escape_string($field->name)."` ".$conn->real_escape_string($field->definition);
										if (!$conn->query("DELETE FROM module_fields WHERE namespace='".$conn->real_escape_string($json->namespace)."' AND name='".$conn->real_escape_string($field->name)."';"))
										{
											//Oh crap we haz an error!
											$continue = 0;
											echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
											foreach ($undo_queries as $query) {
												$conn->query($query);
											}
											break;
										} else {
											$undo_queries[] = "INSERT INTO module_fields (`namespace`, `name`, `type`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($field->name)."', 'postfield');";
										}
									}

								}
							}
						}
						//Create permissions
						if ($continue)
						{
							if ((!empty($json->new_permissions)) && (is_array($json->new_permissions)))
							{
								$id = $conn->query("SELECT * FROM permissions_categories WHERE name='module';")->fetch_assoc()['id'];
								foreach ($json->new_permissions as $permission) {
									if (!$conn->query("DELETE FROM `permissions` WHERE name='".$conn->real_escape_string($permission->name)."';"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "INSERT INTO `permissions` (`name`, `description`, `category`) VALUES ('".$conn->real_escape_string($permission->name)."', '".$conn->real_escape_string($permission->description)."', ".$id.")";
									}

								}
							}
						}
						//Create config
						if ($continue)
						{
							if ((!empty($json->new_config)) && (is_array($json->new_config)))
							{
								foreach ($json->new_config as $config) {
									if (!$conn->query("DELETE FROM `module_config` WHERE name='".$conn->real_escape_string($config->name)."' AND namespace='".$conn->real_escape_string($json->namespace)."';"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "INSERT INTO `module_config` (`namespace`, `name`, `description`, `default_value`, `value`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($config->name)."', '".$conn->real_escape_string($config->description)."', '".$conn->real_escape_string($config->default_value)."', '".$conn->real_escape_string($config->default_value)."')";
									}

								}
							}
						}
						//Create board config
						if ($continue)
						{
							if ((!empty($json->new_boardconfig)) && (is_array($json->new_boardconfig)))
							{
								foreach ($json->new_boardconfig as $config) {
									if (!$conn->query("DELETE FROM `module_boardconfig` WHERE name='".$conn->real_escape_string($config->name)."' AND namespace='".$conn->real_escape_string($json->namespace)."';"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "INSERT INTO `module_boardconfig` (`namespace`, `name`, `description`, `default_value`, `value`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($config->name)."', '".$conn->real_escape_string($config->description)."', '".$conn->real_escape_string($config->default_value)."', '".$conn->real_escape_string($config->default_value)."')";
										if (!$conn->query("ALTER TABLE posts DROP `".$conn->real_escape_string($config->name)."`;"))
										{
											//Oh crap we haz an error!
											$continue = 0;
											echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
											foreach ($undo_queries as $query) {
												$conn->query($query);
											}
											break;
										} else {
											$undo_queries[] = "ALTER TABLE boards ADD `".$conn->real_escape_string($config->name)."` ".$conn->real_escape_string($config->definition);
										}
									}

								}
							}
						}
						//Create new classes
						if ($continue)
						{
							if ((!empty($json->new_classes)) && (is_array($json->new_classes)))
							{
								foreach ($json->new_classes as $class) {
									if (!$conn->query("DELETE FROM `module_classes` WHERE name='".$conn->real_escape_string($class->name)."';"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "INSERT INTO `module_classes` (`namespace`, `name`, `file`, `class`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($class->name)."', '".$conn->real_escape_string($class->file)."', '".$conn->real_escape_string($class->class)."')";
									}
								}
							}
						}
						//Create panel pages
						if ($continue)
						{
							if ((!empty($json->panel_pages)) && (is_array($json->panel_pages)))
							{
								foreach ($json->panel_pages as $page) {
									if (!$conn->query("DELETE FROM `module_pages` WHERE url='".$conn->real_escape_string($page->url)."' AND namespace='".$conn->real_escape_string($json->namespace)."';"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "INSERT INTO `module_pages` (`namespace`, `url`, `file`, `class`, `method`) VALUES ('".$conn->real_escape_string($page->namespace)."', '".$conn->real_escape_string($page->url)."', '".$conn->real_escape_string($page->file)."', '".$conn->real_escape_string($page->class)."', '".$conn->real_escape_string($page->method)."')";
									}
								}
							}
						}
						//Create events
						if ($continue)
						{
							if ((!empty($json->events)) && (is_array($json->events)))
							{
								foreach ($json->events as $event) {
									if (!$conn->query("DELETE FROM `module_events` WHERE event='".$conn->real_escape_string($event->event)."' AND namespace='".$conn->real_escape_string($json->namespace)."';"))
									{
										//Oh crap we haz an error!
										$continue = 0;
										echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
										foreach ($undo_queries as $query) {
											$conn->query($query);
										}
										break;
									} else {
										$undo_queries[] = "INSERT INTO `module_events` (`namespace`, `event`, `file`, `class`, `method`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($event->event)."', '".$conn->real_escape_string($event->file)."', '".$conn->real_escape_string($event->class)."', '".$conn->real_escape_string($event->method)."')";
									}
								}
							}
						}

						//Add to modules table
						if ($continue)
						{
							if (!$conn->query("DELETE FROM `modules` WHERE namespace='".$conn->real_escape_string($json->namespace)."';"))
							{
								//Oh crap we haz an error!
								$continue = 0;
								echo "<b style='color:red;'>".$lang['mod/module_uninstall_error'].$conn->error."</b>";
								foreach ($undo_queries as $query) {
									$conn->query($query);
								}
								break;
							} else {
								$undo_queries[] = "INSERT INTO `modules` (`namespace`, `name`, `description`, `author`, `version`) VALUES ('".$conn->real_escape_string($json->namespace)."', '".$conn->real_escape_string($json->name)."', '".$conn->real_escape_string($json->description)."', '".$conn->real_escape_string($json->author)."', '".$conn->real_escape_string($json->version)."');";
							}
						}


						//Execute install.php (create extra tables etc.)
						if ($continue)
						{
							include($dir."/install.php");
							$installer = new $json->install_class($conn, $mitsuba);
							$installer->uninstall();
							echo "<b style='color: green;'>".$lang['mod/module_uninstalled']."</b>";
						}
					} else {
						echo "<b style='color: red;'>".$lang['mod/module_invalid_json']."</b>";
					}
				} else {
					echo "<b style='color: red;'>".$lang['mod/module_no_file']."</b>";
				}
			}
		}
		
		if ((!empty($_GET['del'])) && ($_GET['del'] == 1) && (!empty($_GET['n'])))
		{
			$mitsuba->admin->reqPermission("modules.delete");
			$result = $conn->query("SELECT * FROM modules WHERE namespace='".$conn->real_escape_string($_GET['n'])."';");
			if ($result->num_rows == 1)
			{
				echo "<b style='color: red;'>".$lang['mod/module_installed_delete']."</b>";
			} else {
				$dir = "./modules/".$_GET['n'];
				$mitsuba->common->delTree("./".$dir);
				echo "<b style='color: green;'>".$lang['mod/module_deleted']."</b>";
			}
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/manage_modules']); ?>

<table>
<thead>
<tr>
<td><?php echo $lang['mod/name']; ?></td>
<td><?php echo $lang['mod/description']; ?></td>
<td><?php echo $lang['mod/author']; ?></td>
<td><?php echo $lang['mod/version']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$installed = array();
$result = $conn->query("SELECT * FROM modules ORDER BY name ASC");
while ($row = $result->fetch_assoc())
{
$installed[$row['namespace']] = 1;
echo "<tr>";
echo "<td class='text-center'>".htmlspecialchars($row['name'])."</td>";
echo "<td>".htmlspecialchars($row['description'])."</td>";
echo "<td class='text-center'>".htmlspecialchars($row['author'])."</td>";
echo "<td class='text-center'>".htmlspecialchars($row['version'])."</td>";
echo "<td class='text-center'><a href='?/modules&unins=1&n=".$row['namespace']."'>".$lang['mod/uninstall']."</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>
<br /><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/uninstalled_modules']); ?>
<table>
<thead>
<tr>
<td class='text-center'><?php echo $lang['mod/name']; ?></td>
<td><?php echo $lang['mod/description']; ?></td>
<td class='text-center'><?php echo $lang['mod/author']; ?></td>
<td class='text-center'><?php echo $lang['mod/version']; ?></td>
<td class='text-center'><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$dirs = array_filter(glob('./modules/*'), 'is_dir');
foreach ($dirs as $dir) {
	if (file_exists($dir."/module.json"))
	{
		$json = json_decode(file_get_contents($dir."/module.json"));
		if ((!is_null($json)) && (!empty($json->name)) && (!empty($json->namespace)) && (!empty($json->description)) && (!empty($json->author)) && (!empty($json->version)) && (!empty($json->install_class)) && (!empty($json->config_class)))
		{
			if (!empty($installed[$json->namespace]))
			{
				continue;
			}
			echo "<tr>";
			echo "<td class='text-center'>".$json->name."</td>";
			echo "<td>".$json->description."</td>";
			echo "<td class='text-center'>".$json->author."</td>";
			echo "<td class='text-center'>".$json->version."</td>";
			echo "<td class='text-center'><a href='?/modules&ins=1&n=".$json->namespace."'>".$lang['mod/install']."</a> <a href='?/modules&del=1&n=".$json->namespace."'>".$lang['mod/delete']."</a></td>";
			echo "</tr>";
		}
	}
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>
<br /><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/upload_module']); ?>

<form action="?/modules" method="POST" enctype="multipart/form-data">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
<input type="hidden" name="mode" value="upload">
<?php echo $lang['mod/file']; ?>: <input id="postFile" name="upfile" type="file"><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>