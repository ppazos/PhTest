<?php

namespace tests\suite5;

use \CaboLabs\Debbie\DebbieTestCase;

class TestCase51 extends DebbieTestCase {

   public function test_does_not_exist_file()
   {
      $directory = '/false/path/file.txt';

      if ($handle = opendir($directory)) {

          while (false !== ($entrada = readdir($handle))) {
              echo "$entrada\n";
          }
          closedir($handle);
      }
   }

   public function test_warning()
   {
    trigger_error("This is an intentional warning", E_USER_WARNING);
   }
}

?>