<?php

namespace CaboLabs\PhTest;

use \CaboLabs\PhBasic\BasicString as str;

class PhTestRun {

   // root folder where all the test suites have their own folder
   private $test_suite_root = './tests';

   private $reports = array();

   private $after_each_test_function;

   public function init($test_suite_root = './tests')
   {
      if (!is_dir($test_suite_root))
      {
         echo "Folder $test_suite_root doesn't exist\n";
         exit;
      }

      if (!str::endsWith($test_suite_root, DIRECTORY_SEPARATOR))
      {
         $test_suite_root .= DIRECTORY_SEPARATOR;
      }

      $this->test_suite_root = $test_suite_root;
   }

   public function run_all()
   {
      $root_dir = dir($this->test_suite_root);

      if ($root_dir === FALSE)
      {
         echo "Can't read ". $root_dir ."\n";
         exit();
      }

      while (false !== ($test_suite = $root_dir->read()))
      {
         if (!is_dir($this->test_suite_root . $test_suite) ||
             in_array($test_suite, array('.', '..', 'data'))) continue;

         $path = $this->test_suite_root . $test_suite;

         if (!str::endsWith($path, DIRECTORY_SEPARATOR))
         {
            $path .= DIRECTORY_SEPARATOR;
         }

         $suite_dir = dir($path);

         if ($suite_dir === FALSE)
         {
            echo "Can't read ". $suite_dir ."\n";
            exit();
         }

         $test_cases = array();

         // $test_case is a class name
         while (false !== ($test_case = $suite_dir->read()))
         {
            // only php files are valid test cases
            if (preg_match('/\.php$/', $test_case))
            {
               $test_case_path = $path . $test_case;

               $namespaced_class = substr(str_replace(['./', '/'], ['', '\\'], $test_case_path), 0, -4);
               
               if (is_file($test_case_path))
               {
                  $test_cases[$namespaced_class] = $test_case_path;
               }
            }
         }

         $suite_dir->close();

         $phsuite = new PhTestSuite($test_suite, $test_cases);
         $phsuite->run($this->after_each_test_function);
         $this->reports[] = $phsuite->get_reports();
      }

      // TODO: should close all directory handles!
      $root_dir->close();
   }

   public function run_suite($suite)
   {
      // runs all cases in the suite
      $this->run_cases($suite);
   }

   public function run_suites(...$suites)
   {
      // TODO
   }

   public function run_case($suite, $case, $method = NULL)
   {
      $path = $this->test_suite_root . $suite;

      if (!str::endsWith($path, DIRECTORY_SEPARATOR))
      {
         $path .= DIRECTORY_SEPARATOR;
      }

      $test_case_path = $path . $case . '.php';

      $namespaced_class = substr(str_replace(['./', '/'], ['', '\\'], $test_case_path), 0, -4);

      $test_cases = array();
      if (is_file($test_case_path))
      {
         $test_cases[$namespaced_class] = $test_case_path;
      }

      $phsuite = new PhTestSuite($suite, $test_cases);
      $phsuite->run($this->after_each_test_function, $method);
      $this->reports[] = $phsuite->get_reports();
   }

   public function run_cases($suite, ...$cases)
   {
      $path = $this->test_suite_root . $suite;

      if (!str::endsWith($path, DIRECTORY_SEPARATOR))
      {
         $path .= DIRECTORY_SEPARATOR;
      }

      $suite_dir = dir($path);

      if ($suite_dir === FALSE)
      {
         echo "Can't read ". $suite_dir ."\n";
         exit();
      }
      
      $test_cases = array();

      // $test_case is a class name
      while (false !== ($test_case = $suite_dir->read()))
      {
         if (empty($cases))
         {
            $test_case_path = $path . $test_case;
         }
         else
         {
            $test_case_path = $path . $cases[0] . '.php';
         }
         
         $namespaced_class = substr(str_replace(['./', '/'], ['', '\\'], $test_case_path), 0, -4);
           
         if (is_file($test_case_path))
         {
            $test_cases[$namespaced_class] = $test_case_path;
         }
   
      }

      $suite_dir->close();

      $phsuite = new PhTestSuite($suite, $test_cases);
      $phsuite->run($this->after_each_test_function);
      $this->reports[] = $phsuite->get_reports();
   }

   public function render_reports($test_time)
   {
      global $total_suites, $total_cases, $total_tests, $total_asserts, $total_failed, $total_successful;

      $total_cases_failed = $total_cases_successful = array();

      echo 'Test report: '. PHP_EOL . PHP_EOL;
      
      foreach ($this->reports as $i => $test_suite_reports)
      {
         $total_suites ++;

         foreach ($test_suite_reports as $test_case => $reports)
         {
            $successful = 0;
            $failed = 0;

            echo '├── Test case: '. $test_case .'  ── Total case: '. count($reports) . ' time:xxx' . PHP_EOL;
            echo '|   |'. PHP_EOL;

            $total_cases ++;

            foreach ($reports as $test_function => $report)
            {

               echo '|   ├── Test: '. $test_function . PHP_EOL;

               $total_tests ++;

               //print_r($report['asserts']);

               if (isset($report['asserts']))
               {
                  foreach ($report['asserts'] as $assert_report)
                  {
                     if ($assert_report['type'] == 'ERROR')
                     {
                        echo '|   |   |'. PHP_EOL;
                        echo "|   |   └── \033[91mERROR: ". $assert_report['msg'] ."\033[0m". PHP_EOL;
                     
                        $total_failed ++;
                        $failed ++;
                     }
                     else if ($assert_report['type'] == 'OK')
                     {
                        echo '|   |   |'. PHP_EOL;
                        echo "|   |   └── \033[92mOK: ". $assert_report['msg'] ."\033[0m". PHP_EOL;
                     
                        $total_successful ++;
                        $successful ++;
                     }
                     else if ($assert_report['type'] == 'EXCEPTION')
                     {
                        echo '|   |   |'. PHP_EOL;
                        echo "|   |   └── \033[94mEXCEPTION: ". $assert_report['msg'] ."\033[0m". PHP_EOL;
                     }
                  }

                  $total_asserts ++;
               }

               if (!empty($report['output']))
               {
                  echo '|   |   |'. PHP_EOL;
                  echo '|   |   └── OUTPUT: '. $report['output'] . PHP_EOL;
               }

               echo '|   |'. PHP_EOL;

            }

            if ($total_failed >= 1)
            {
               $total_cases_failed[] = [
                  'case' => $test_case,
                  'case_failed' => $failed, 
                  'case_successful' => $successful
               ];
            }
            else
            {
               $total_cases_successful[] = [
                  'case' => $test_case, 
                  'case_failed' => $failed, 
                  'case_successful' => $successful
               ];
            }
            
         }
      }

      echo PHP_EOL;
      
      $this->get_summary_report($test_time, $total_suites, $total_cases, $total_tests, $total_asserts, $total_failed, $total_successful, $total_cases_failed, $total_cases_successful);
   
   }

   public function render_reports_html($path, $test_time)
   {
      global $html_report, $content, $total_suites, $total_cases, $total_tests, $total_asserts, $total_failed, $total_successful;

      $total_cases_failed = $total_cases_successful = array();
      
      $html_report = '<h1>Test report<h1>';

      $item3 ="";
      $item4 ="";
      
      foreach ($this->reports as $i => $test_suite_reports)
      {
         $html_report .= '<ul>';
         $total_suites ++;

         foreach ($test_suite_reports as $test_case => $reports)
         {
            $successful = 0;
            $failed = 0;
            
            $html_report .= '<ul>';
            $html_report .= '<li class="container"><p>Test case: '. $test_case .'</p>';

            $total_cases ++;

            foreach ($reports as $test_function => $report)
            {
               $html_report .= '<ul>';
               $html_report .= '<li class="container" style="margin-top: 10px;"><p>Test: '. $test_function .'</p>';

               $total_tests ++;

               if (isset($report['asserts']))
               {
                  foreach ($report['asserts'] as $assert_report)
                  {
                     if ($assert_report['type'] == 'ERROR')
                     {
                        $html_report .= '<li><p style="color:red">ERROR: '. $assert_report['msg'] .'</p></li>';

                        $total_failed ++;
                        $failed ++;
                     }
                     else if ($assert_report['type'] == 'OK')
                     {
                        $html_report .= '<li><p style="color:green">OK: '. $assert_report['msg'] .'</p></li>';

                        $total_successful ++;
                        $successful ++;
                     }
                     else if ($assert_report['type'] == 'EXCEPTION')
                     {
                        $html_report .= '<li><p style="color:blue">EXCEPTION: '. $assert_report['msg'] .'</p></li>';
                     }

                     if (!empty($report['output']))
                     {
                        $html_report .= '<li><p style="color:gray">OUTPUT: '. $report['output'] .'</p></li>';
                     }
                  }

                  $total_asserts ++;
               }

               $html_report .= '</li>';
               $html_report .= '</ul>';
            }
            $html_report .= '</li><br>';
            $html_report .= '</ul>';

            if ($total_failed >= 1)
            {
               $total_cases_failed[] = [
                  'case' => $test_case,
                  'case_failed' => $failed, 
                  'case_successful' => $successful
               ];
            }
            else
            {
               $total_cases_successful[] = [
                  'case' => $test_case, 
                  'case_failed' => $failed, 
                  'case_successful' => $successful
               ];
            }
         }
         $html_report .= '</ul><br>';
      }

      if (count($total_cases_failed) >= 1)
      {

         $failed_cases = count($total_cases_failed);
         
         foreach ($total_cases_failed as $total_case_failed)
         {
            $item3 .= "
            <div style='text-align: left;'>- ". $total_case_failed['case'] .": 
             asserts failed: ". $total_case_failed['case_failed'] ." / 
             asserts successful: ". $total_case_failed['case_successful'] ."
            </div>";
         }
      }
      else
      {
         $failed_cases = 0;
      }

      if (count($total_cases_successful) >= 1)
      {
         $successful_case = count($total_cases_successful);

         foreach ($total_cases_successful as $total_case_successful)
         {
            $item4 .= "<div style='text-align: left;'>- ". $total_case_successful['case'].": 
             asserts failed: ". $total_case_successful["case_failed"] ." / 
             asserts successful: ". $total_case_successful["case_successful"] ."
            </div>";
           
         }
      }
      else
      {
         $successful_case = 0;
      }

      //css provisional
      $content = <<< EOD
         <!DOCTYPE html>
         <html lang="en">
         <head>
         <meta charset="UTF-8">
         <meta http-equiv="X-UA-Compatible" content="IE=edge">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <title>Document</title>
         </head>
         <style>
         body{
            font-size: 10px;
            line-height: 35px;
         }
         ul,
         li {
         list-style: none;
         margin: 0;
         padding: 0;
         }
         ul {
         padding-left: 1em;
         }
         li {
         padding-left: 1em;
         border: 1px dotted black;
         border-width: 0 0 1px 1px;
         }
         li.container {
         border-bottom: 0px;
         }
         li.empty {
         font-style: italic;
         color: silver;
         border-color: silver;
         }
         li p {
         margin: 0;
         background: white;
         position: relative;
         top: 0.5em;
         }
         li ul {
         border-top: 1px dotted black;
         margin-left: -1em;
         padding-left: 2em;
         }
         ul li:last-child ul {
         border-left: 1px solid white;
         margin-left: -17px;
         }

         .grid-container {
            display: grid;
            gap: 10px;
            padding: 10px;
          }
          
          .grid-item {
            padding: 20px;
            border: 1px dotted black;
            text-align: center;
          }
          
          .item1 {
            grid-column: 1;
            grid-row: 1;
          }
          
          .item2 {
            grid-column: 2;
            grid-row: 1;
          }
          
          .item3 {
            grid-column: 3;
            grid-row: 1;
          }
          .item4 {
            grid-column: 4;
            grid-row: 1;
          }
         </style><body>

         <div class="grid-container">
         <div class="grid-item item1">
            <h1>Total suites: $total_suites </h1>
         </div>
         <div class="grid-item item2">
            <h1>Total tests cases: $total_cases </h1>
         </div>
         <div class="grid-item item3">
            <h1>Cases failed: $failed_cases</h1>
         </div>  
         <div class="grid-item item4">
            <h1>Cases successful: $successful_case</h1>
         </div> 
         </div>

         $html_report

         <h1>Summary</h1>
         <div class="grid-container">
         <div class="grid-item item1">
            <h1>Total suites: $total_suites </h1>
            <h2> total time:  $test_time μs</h2>
         </div>
         <div class="grid-item item2">
            <h1>Total tests cases: $total_cases </h1>
            <div style="text-align: left;">Total tests: $total_tests
            <p>asserts failed: $total_failed</p>
            <p>asserts successful: $total_successful</p>
            <p>Total asserts: $total_asserts</p>
            </div>
         </div>
         <div class="grid-item item3">
            <h1>Cases failed: $failed_cases</h1>
            $item3
         </div>  
         <div class="grid-item item4">
            <h1>Cases successful: $successful_case</h1>
            $item4
         </div> 
         </div>
         
         </body></html>
         EOD;
      // end css provisional

      if ($path == './')
      {
         $path = 'test_report.html';
      }

      file_put_contents($path, $content);
   }

   public function get_summary_report($test_time, $total_suites, $total_cases, $total_tests, $total_asserts, $total_failed, $total_successful, $total_cases_failed, $total_cases_successful)
   {
      echo 'Summary reports: '. PHP_EOL . PHP_EOL;

      echo 'Tests reports - Total suites: '.  $total_suites .'  --> total time: '. $test_time .  ' μs' .PHP_EOL;

      echo PHP_EOL;

      echo 'Total tests cases: '. $total_cases . PHP_EOL;

      echo PHP_EOL;

      echo 'Total tests: '. $total_tests . PHP_EOL;

      echo PHP_EOL;

      echo '  asserts failed: '. $total_failed . PHP_EOL;

      echo PHP_EOL;

      echo '  asserts successful: '. $total_successful . PHP_EOL;

      echo PHP_EOL;

      echo '  Total asserts: '. $total_asserts . PHP_EOL;

      echo PHP_EOL;

      echo PHP_EOL;

      if (count($total_cases_failed) >= 1)
      {

         echo 'Cases failed: ('. count($total_cases_failed) . ')'. PHP_EOL;

         echo PHP_EOL;

         foreach ($total_cases_failed as $total_case_failed)
         {
            echo '  '. $total_case_failed["case"] . PHP_EOL;

            echo PHP_EOL;

            echo '    asserts failed: '. $total_case_failed["case_failed"] . PHP_EOL;

            echo PHP_EOL;

            echo '    asserts successful: '. $total_case_failed["case_successful"] . PHP_EOL;

            echo PHP_EOL;
         }
      }
      else
      {
         echo 'Cases failed: 0' . PHP_EOL;

         echo PHP_EOL;
      }

      if (count($total_cases_successful) >= 1)
      {
         echo 'Cases successful: ('. count($total_cases_successful) . ')'. PHP_EOL;

         echo PHP_EOL;

         echo PHP_EOL;

         foreach ($total_cases_successful as $total_case_successful)
         {
            echo '  '. $total_case_successful["case"] . PHP_EOL;

            echo PHP_EOL;

            echo '    asserts failed: '. $total_case_successful["case_failed"] . PHP_EOL;

            echo PHP_EOL;

            echo '    asserts successful: '. $total_case_successful["case_successful"] . PHP_EOL;

            echo PHP_EOL;
         }
      }
      else
      {
         echo 'Cases successful: 0' . PHP_EOL;

         echo PHP_EOL;
      }

      echo PHP_EOL;   
   }

   public function get_reports()
   {
      return $this->reports;
   }

   public function after_each_test($callback)
   {
      $this->after_each_test_function = $callback;
   }
}

?>