Mitsuba module API rev 1
========================

Modules should be packed into zip files which structure looks like this:

    |
    |- module.json
    |- install.php
    |- config.php
    |- *.php
    |- res/
     |- *.jpg; *.gif; *.png
     |- *.css
     |- *.js

module.json
-----------

*module.json* is a file which contains information about the module

| **name**           | **value type** | **description**      | **possible values**  | **example value**                          | **required** |
|:-------------------|:---------------|:---------------------|:---------------------|:-------------------------------------------|:-------------|
| `name`             | `string`       | Module name          | text                 | `Dice`                                     | **yes**      |
| `namespace`        | `string`       | Module namespace (used in permissions) | text  | `dice`                                  | **yes**      |
| `description`      | `string`       | Module description   | text                 | `Dice roller module for games boards`      | **yes**      |
| `author`           | `string`       | Module author        | text                 | `desuneko`                                 | **yes**      |
| `version`          | `string`       | Module version       | text                 | `1.2.3`                                    | **yes**      |
| `install_class`    | `string`       | install.php class name | text               | `\Desuneko\Modules\Dice\Installer`         | **yes**      |
| `config_class`     | `string`       | config.php class name | text                | `\Desuneko\Modules\Dice\Config`            | **yes**      |
| `website`          | `string`       | Module's website     | text                 | `http://github.com/MitsubaBBS/Mitsuba`     | **no**       |
| `requirements`     | `array`  | Module requirements **[not implemented yet]** | array of text | `["desuneko.main", "desuneko.plus"]` | **no** |
| `new_post_fields`  | `array`        | New post fields      | array of text        | See **new post fields**                    | **no**       |
| `new_permissions`  | `array`        | New permissions      | array of text        | See **new permissions**                    | **no**       |
| `new_config`       | `array`        | New config entries   | array                | See **new config entries**                 | **no**       |
| `new_boardconfig`  | `array` | New board config fields **[not implemented yet]** | array | See **new board config entries**    | **no**       |
| `new_classes`      | `array`        | New Mitsuba classes  | array of new classes | See **new classes**                        | **no**       |
| `new_tables`       | `array`        | New database tables  | array of text        | `["roll_logs"]`                            | **no**       |
| `panel_pages`      | `array`        | New panel pages      | array                | See **new panel pages**                    | **no**       |
| `events`           | `array`        | Events               | array                | See **events**                             | **no**       |

### Requirements

Not implemented.

### New post fields

Those are added to the `posts` table, so make names unique as possible.

| **name**           | **value type** | **description**      | **possible values**  | **example value**                          | **required** |
|:-------------------|:---------------|:---------------------|:---------------------|:-------------------------------------------|:-------------|
| `name`             | `string`       | Name                 | text                 | `roll_result`                              | **yes**      |
| `definition`       | `string`       | Column definition    | text                 | `varchar(80) NOT NULL`                     | **yes**      |

### New permissions

| **name**           | **value type** | **description**      | **possible values**  | **example value**                          | **required** |
|:-------------------|:---------------|:---------------------|:---------------------|:-------------------------------------------|:-------------|
| `name`             | `string`       | Permission name      | text                 | `roll`                                     | **yes**      |
| `description`      | `string`       | Description          | text                 | `User can roll dice`                       | **yes**      |

You can then use this permission as on the following example:

    $canRoll = $this->mitsuba->admin->checkPermission("module.dice.roll"); //module.{namespace}.{permission_name}

Or:

    $this->mitsuba->admin->reqPermission("module.dice.roll");

### New config entries

| **name**           | **value type** | **description**      | **possible values**  | **example value**                          | **required** |
|:-------------------|:---------------|:---------------------|:---------------------|:-------------------------------------------|:-------------|
| `name`             | `string`       | Permission name      | text                 | `text_color`                               | **yes**      |
| `description`      | `string`       | Description          | text                 | `Color of dice roll result`                | **yes**      |
| `type`             | `string`       | Type of field        | string, boolean      | `string`                                   | **yes**      |
| `default_value`    | `string`       | Default value        | text                 | `#FFFFFF`                                  | **yes**      |

You can access config values by using `$this->mitsuba->config['{NAMESPACE}.{CONFIG_NAME}']` (e.g. `$this->mitsuba->config['dice.text_color']`).

### New board config entries

**Althrough they have own table and they are mostly implemented, they won't work.**

| **name**           | **value type** | **description**      | **possible values**  | **example value**                          | **required** |
|:-------------------|:---------------|:---------------------|:---------------------|:-------------------------------------------|:-------------|
| `name`             | `string`       | Name                 | text                 | `rolling_enabled`                          | **yes**      |
| `description`      | `string`       | Description          | text                 | `Dice rolling enabled`                     | **yes**      |
| `type`             | `string`       | Type of field        | string, checkbox, integer | `checkbox`                            | **yes**      |
| `definition`       | `string`       | Column definition    | text                 | `int(1) NOT NULL`                          | **yes**      |
| `default_value`    | `string`       | Default value        | text                 | `1`                                        | **yes**      |

### New classes

| **name**           | **value type** | **description**      | **possible values**  | **example value**                          | **required** |
|:-------------------|:---------------|:---------------------|:---------------------|:-------------------------------------------|:-------------|
| `name`             | `string`       | $mitsuba->[...] name | text                 | `dice`                                     | **yes**      |
| `file`             | `string`       | File name/path       | text                 | `newclass.php`, `classes/newclass.php`     | **yes**      |
| `class`            | `string`       | Name of class        | text                 | `\Desuneko\Modules\Dice`                   | **yes**      |

You can access new classes by using `$this->mitsuba->{CLASS_NAME}` (e.g. `$this->mitsuba->dice`).

### New panel pages

| **name**           | **value type** | **description**      | **possible values**  | **example value**                          | **required** |
|:-------------------|:---------------|:---------------------|:---------------------|:-------------------------------------------|:-------------|
| `url`              | `string`       | mod.php?URL          | text                 | `/dice`                                    | **yes**      |
| `file`             | `string`       | File name/path       | text                 | `newclass.php`, `classes/newclass.php`     | **yes**      |
| `class`            | `string`       | Name of class        | text                 | `\Desuneko\Modules\Dice`                   | **yes**      |
| `method`           | `string`       | Name of method       | text                 | `showDicePage`                             | **yes**      |

### Events

Before you can use events, you'll need to declare them, so Mitsuba can know when to run your script.

| **name**           | **value type** | **description**      | **possible values**  | **example value**                          | **required** |
|:-------------------|:---------------|:---------------------|:---------------------|:-------------------------------------------|:-------------|
| `event`            | `string`       | Event name           | text                 | `posting.newpost`                          | **yes**      |
| `file`             | `string`       | File name/path       | text                 | `events.php`, `inc/events.php`             | **yes**      |
| `class`            | `string`       | Name of class        | text                 | `\Desuneko\Modules\Dice\Events`            | **yes**      |
| `method`           | `string`       | Name of method       | text                 | `onPost`                                   | **yes**      |

### Example module.json

    {
        "name": "Dice",
        "namespace": "desuneko.dice",
        "description": "Dice roller module for games boards",
        "author": "desuneko",
        "version": "1.2.3",
        "install_class": "\\Desuneko\\Modules\\Dice\\Installer",
        "config_class": "\\Desuneko\\Modules\\Dice\\Config",
        "new_config": [
            {
                "name": "text_color",
                "description": "Text color of dice roll result",
                "type": "text",
                "default_value": "#FFFFFF"
            },
            {
                "name": "text_font",
                "description": "Font of dice roll result",
                "type": "text",
                "default_value": "sans-serif"
            }
        ]
    }

install.php and config.php
--------------------------

**install.php** should contain `install($conn)` and `uninstall($conn)` public methods, *install* method should only create extra tables, because new post fields, board config fields, configuration fields, permissions, classes and events are created automatically. *uninstall* method should remove extra tables.

**config.php** should contain public method `showConfigurationPage()` which should show page for module configuration

Both of them should have a constructor: `public function __construct($conn, &$mitsuba)`.

**Example config.php:**

    <?php
    namespace Desuneko\Modules\Dice;
    class Config {
        private $conn;
        private $mitsuba;

        public function __construct($conn, &$mitsuba) {
            $this->conn = $conn;
            $this->mitsuba = $mitsuba;
        }

        public function showConfigurationPage()
        {
            $mitsuba->admin->reqPermission("config.update");
            $mitsuba->admin->ui->startSection("Dice config");
            ?>
            Hello world!
            <?php
            $mitsuba->admin->ui->endSection();
        }
    }
    ?>

Other PHP files
---------------

**New class** and **event** files should contain a constructor: `public function __construct($conn, &$mitsuba)`.

Events
------

*imgboard* - those are triggered in imgboard.php

  * *imgboard.begin* - triggered after connecting to database, arguments: `requestdata`
    * *requestdata* - POST variables
  * *imgboard.mode* - triggered in `default:` in `switch ($mode)`, arguments: `mode, requestdata`
    * *mode* - `$_POST['mode']`
    * *requestdata* - POST variables

*posting* - those are triggered in inc/posting.php

  * *posting.post* - triggered after escaping <+'s in `addPost`, arguments: `postdata, requestdata`
    * *postdata* - all `addPost`'s arguments, you can make direct changes to this array
    * *requestdata* - POST variables
  * *posting.delete* - triggered after deleting posts, arguments: `postno`, `onlyimgdel`

*mitsuba* - those are triggered in inc/mitsuba.php

  * *permission* - triggered in `reqPermission` when permission was requested, arguments: `permission`
    * *permission* - permission string (e.g. `config.view`)

**Example event function:**

    function onPost($event, &$eventdata)
    {
        //$eventdata contains postdata (&array) and requestdata (array)
        //we can edit postdata, because it is a pointer
        $eventdata['postdata']['subject'] = "Hello world!";
    }