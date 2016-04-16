<?php

class DropBox {

    static function Load() {
    }

    static function Info() {
        return array (
            'name'    => 'DropBox',
            'author'  => 'Alexandre Girard',
            'version' => '1.0.0',
            'site'    => 'http://alexgirard.com/',
            'notes'   => 'Convert video found inside a filesystem dropbox'
        );
    }

    static function Install() {
      Settings::Set ("dropbox_path", "/please/set/correct/path");
    }

    static function Uninstall() {
      Settings::Remove ("dropbox_path");
    }

    static function displayDropboxCategory($path) {
      App::LoadClass ('Settings');

      if (substr($path, -1) != '/') {
        $path .= '/';
      }

      $dropbox_path = Settings::Get ('dropbox_path');
      $allowed_extension = array('mov', 'avi', 'mp4');

      if($path == $dropbox_path) {
        $category = "general";
      } else {
        $category = basename($path);
      }

      //get all files in specified directory
      $files = glob($path . "*");
      $nb_files = 0;

      foreach($files as $file) {
        //check to see if the file is a folder/directory
        if(!is_dir($file)) {
          $extension = pathinfo($file, PATHINFO_EXTENSION);
          if (in_array($extension, $allowed_extension)) {
            $nb_files += 1;
          }
        }
      }

      if($nb_files > 0) {

?>
<div class="block list">
<h3>Category : <?= ucwords($category) ?> - <?= $nb_files ?> videos</h3>
  <form method="post">
    <input type="hidden" name="dropbox_import" value="true" />
    <input type="hidden" name="dropbox_file" value="all" />
    <input type="hidden" name="dropbox_import_path" value="<?=$path ?>" />
    <input class="button" type="submit" value="Import All" style="float: right;right: 20px;position: relative;"/>
  </form>
  <table>
    <thead>
      <tr>
        <td class="large">Filename</td>
      </tr>
    </thead>
    <tbody>
  <?php
  //print each file name
  foreach($files as $file) {
    //check to see if the file is a folder/directory
    if(!is_dir($file)) {
      $extension = pathinfo($file, PATHINFO_EXTENSION);
      if (in_array($extension, $allowed_extension)) {
        $odd = empty ($odd) ? true : false;
  ?>
    <tr class="<?=$odd ? 'odd' : ''?>">
      <td class="video-title"><?= basename($file) ?></td>
    </tr>
  <?php
      }
    }
  }
  ?>
    </tbody>
  </table>
</div>
<?php
      }
    }


    static function Settings() {
      App::LoadClass ('Settings');

      $dropbox_path = Settings::Get ('dropbox_path');
      $allowed_extension = array('mov', 'avi', 'mp4');
      $message = null;

      if (isset ($_POST['dropbox_settings']) ) {

        // Set dropbox path
        if (!empty($_POST['dropbox_path']) && !ctype_space ($_POST['dropbox_path'])) {
          $dropbox_path = $_POST['dropbox_path'];
          if (substr($dropbox_path, -1) != '/') {
            $dropbox_path .= '/';
          }
          Settings::Set('dropbox_path', $dropbox_path);
          $message = 'Settings have been updated.';
          $message_type = 'success';
        }

      }

      if (isset ($_POST['dropbox_import']) ) {

        exec( dirname(__FILE__).'/dropbox.sh' );

      }

?>
<h1>DropBox Plugin</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>

<div class="block">

  <form method="post">
    <div class="row">
      <label>Dropbox path :</label>
      <input type="text" name="dropbox_path" value="<?php echo $dropbox_path; ?>" class="text"/>
    </div>
    <div class="row-shift">
      <input type="hidden" name="dropbox_settings" value="true" />
      <input class="button" type="submit" value="Update Plugin Settings" />
    </div>
  </form>

</div>

<?php

    if($handle = opendir($dropbox_path)) {

      DropBox::displayDropboxCategory($dropbox_path);

      //get all files in specified directory
      $files = glob($dropbox_path . "*");

      //print each file name
      foreach($files as $file) {
        //check to see if the file is a folder/directory
        if(is_dir($file)) {
          DropBox::displayDropboxCategory($file);
        }
      }

    } else {

?>
<div class="block">
<p>Please set a path to display available video in dropbox.</p>
</div>
<?php }

  }
}
