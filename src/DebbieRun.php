<?php

namespace CaboLabs\Debbie;

use \CaboLabs\PhBasic\BasicString as str;

class DebbieRun {

   // root folder where all the test suites have their own folder
   private $test_suite_root = './tests';

   private $reports = [];

   private $after_each_test_function;

   private $execution_time;

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
      $this->execution_time = 0;

      // This is to catch FATAL ERRORS throwing the error as an exception that can be catched and reported as such in the test report.
      $onError = function ($level, $message, $file, $line) {
         throw new \ErrorException($message, 0, $level, $file, $line);
      };

      set_error_handler($onError);

      // try {
      //    set_error_handler($onError);
      //    define(Pi, 3);
      // } catch (\Throwable $throwable) {
      //    echo 'Throwable: ', $throwable->getMessage(), "\n";
      // } finally {
      //    restore_error_handler();
      // }
   }

   public function run_all()
   {
      $root_dir = dir($this->test_suite_root);

      if ($root_dir === FALSE)
      {
         echo "Can't read " . $root_dir . "\n";
         exit();
      }

      while (false !== ($test_suite = $root_dir->read()))
      {
         if (
            !is_dir($this->test_suite_root . $test_suite) ||
            in_array($test_suite, array('.', '..', 'data'))
         ) continue;

         $path = $this->test_suite_root . $test_suite;

         if (!str::endsWith($path, DIRECTORY_SEPARATOR))
         {
            $path .= DIRECTORY_SEPARATOR;
         }

         if (is_dir($path))
         {
            $suite_dir = dir($path);
         }
         else
         {
            echo "Folder $path doesn't exist\n";
            exit;
         }

         if ($suite_dir === FALSE)
         {
            echo "Can't read " . $suite_dir . "\n";
            exit();
         }

         $test_cases = [];

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

         $phsuite = new DebbieSuite($test_suite, $test_cases);
         $phsuite->run($this->after_each_test_function);
         $this->reports[] = $phsuite->get_reports(); // NOTE: this adds one report per suite
         $this->execution_time += $phsuite->get_execution_time();
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

      // Check if the file exists before continuing
      if (!is_file($test_case_path))
      {
         echo "Can't read " . $test_case_path . "\n";
         return;
      }

      $namespaced_class = substr(str_replace(['./', '/'], ['', '\\'], $test_case_path), 0, -4);

      $test_cases = [];

      $test_cases[$namespaced_class] = $test_case_path;

      $phsuite = new DebbieSuite($suite, $test_cases);
      $phsuite->run($this->after_each_test_function, $method);
      $this->reports[] = $phsuite->get_reports();
      $this->execution_time = $phsuite->get_execution_time();
   }

   public function run_cases($suite, ...$cases)
   {
      $path = $this->test_suite_root . $suite;

      if (!str::endsWith($path, DIRECTORY_SEPARATOR))
      {
         $path .= DIRECTORY_SEPARATOR;
      }

      if (is_dir($path))
      {
         $suite_dir = dir($path);
      }
      else
      {
         echo "Folder $path doesn't exist\n";
         exit;
      }

      if ($suite_dir === FALSE)
      {
         echo "Can't read " . $suite_dir . "\n";
         exit();
      }

      $test_cases = [];

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

      $phsuite = new DebbieSuite($suite, $test_cases);
      $phsuite->run($this->after_each_test_function);
      $this->reports[] = $phsuite->get_reports();
      $this->execution_time = $phsuite->get_execution_time();
   }

   public function render_reports()
   {
      global $total_suites, $total_cases, $total_tests, $total_asserts, $total_failed, $total_successful;

      $total_cases_failed = $total_cases_successful = [];

      echo 'Test report: ' . PHP_EOL . PHP_EOL;

      foreach ($this->reports as $i => $test_suite_reports)
      {
         $total_suites++;

         foreach ($test_suite_reports as $test_case => $reports)
         {
            $successful = 0;
            $failed = 0;

            echo '├── Test case: ' . $test_case . '  (test count: ' . count($reports) . ')' . PHP_EOL;
            echo '|   |' . PHP_EOL;

            $total_cases++;

            foreach ($reports as $test_function => $report)
            {
               echo '|   ├── Test: ' . $test_function . PHP_EOL;

               $total_tests++;

               //print_r($report['asserts']);

               if (isset($report['asserts']))
               {
                  foreach ($report['asserts'] as $assert_report)
                  {
                     if ($assert_report['type'] == 'ERROR')
                     {
                        echo '|   |   |' . PHP_EOL;
                        echo "|   |   └── \033[91mERROR: " . $assert_report['msg'] . "\033[0m" . PHP_EOL;

                        $total_failed++;
                        $failed++;
                     }
                     else if ($assert_report['type'] == 'OK')
                     {
                        echo '|   |   |' . PHP_EOL;
                        echo "|   |   └── \033[92mOK: " . $assert_report['msg'] . "\033[0m" . PHP_EOL;

                        $total_successful++;
                        $successful++;
                     }
                     else if ($assert_report['type'] == 'EXCEPTION')
                     {
                        echo '|   |   |' . PHP_EOL;
                        echo "|   |   └── \033[94mEXCEPTION: " . $assert_report['msg'] . "\033[0m" . PHP_EOL;
                     }
                  }

                  $total_asserts++;
               }

               if (!empty($report['output']))
               {
                  echo '|   |   |' . PHP_EOL;
                  echo '|   |   └── OUTPUT: '. $report['output'] . PHP_EOL;
               }
               echo '|   |' . PHP_EOL;
            }

            if ($failed > 0)
            {
               $total_cases_failed[] = [
                  'case'            => $test_case,
                  'case_failed'     => $failed,
                  'case_successful' => $successful
               ];
            }

            if ($successful > 0 && $failed == 0)
            {
               $total_cases_successful[] = [
                  'case'            => $test_case,
                  'case_failed'     => $failed,
                  'case_successful' => $successful
               ];
            }
         }
      }

      echo PHP_EOL;

      $this->get_summary_report(
         $total_suites,
         $total_cases,
         $total_tests,
         $total_asserts,
         $total_failed,
         $total_successful,
         $total_cases_failed,
         $total_cases_successful
      );
   }

   public function render_reports_html($path)
   {
      /** @var String $html_report, contains the result of all the report suites, test assets
       *  @var String $menu_items, extract only the suite name
       *  @var String $menu_subitems, extract only the tests name
       *  @var String $failed_Summ, renders the content of the summary table failed cases
       *  @var String $succ_Summ, renders the content of the summary table $successful cases
       *  @var int $successful, count successful asserts per test
       *  @var int $failed, count failed asserts per test
       *  @var String $names, extracts only the names of the suites from the array
       *  @var int $total_cases, stores the total cases number
       *  @var int $total_failed, stores the total failed cases number
       *  @var int $total_successful, stores the total successful cases number
       */

      $total_cases_failed = [];
      $total_cases_successful = [];
      $namesSuitessubmenu = [];
      $arrSummaryTestCase = [];
      $namesSuitesMenu = [];
      $tests_fatal_error = [];
      $tests_type_fail = [];

      $successful_case = 0;
      $failed_cases = 0;
      $total_cases = 0;
      $total_suites = 0;
      $total_tests = 0;
      $total_failed = 0;
      $total_successful = 0;
      $total_asserts = 0;

      $html_report = '';
      $menu_items = '';
      $failed_Summ = "";
      $succ_Summ = "";
      $cards_summary_suites = "";

      foreach ($this->reports as $i => $test_suite_reports)
      {
         $total_suites++;
         $ttests = 1;

         foreach ($test_suite_reports as $test_case => $reports)
         {
            $successful = 0;
            $failed = 0;
            $total_class_test_x_suites = 0;

            $names = explode("\\", $test_case);

            $namesSuitesMenu[] = $names[1];

            $namesSuitessubmenu[] = $test_case;

            $total_cases++;

            foreach ($reports as $test_function => $report)
            {
               $total_tests++;

               if (isset($report['asserts']))
               {
                  foreach ($report['asserts'] as $assert_report)
                  {
                     $total_class_test_x_suites++;
                     if ($assert_report['type'] == 'FAIL') // TODO: EXCEPTION and ERROR should count as fail
                     {
                        $total_failed++;
                        $failed++; //count the assert fail of each test per suite
                     }
                     else if ($assert_report['type'] == 'OK')
                     {
                        $total_successful++;
                        $successful++; //count the assert successful of each test per suite
                     }
                     else if ($assert_report['type'] == 'ERROR') // fatal error
                     {
                        $tests_fatal_error[] = [
                           'case' => $test_case
                        ];
                     }
                     else if ($assert_report['type'] == 'FAIL')
                     {
                        $tests_type_fail[] = [
                           'case' => $test_case
                        ];
                     }
                  }
                  $total_asserts++;
               }
            }

            if ($failed > 0)
            {
               $total_cases_failed[] = [
                  'case'            => $test_case,
                  'case_failed'     => $failed,
                  'case_successful' => $successful
               ];
            }

            if ($successful > 0 && $failed == 0)
            {
               $total_cases_successful[] = [
                  'case'            => $test_case,
                  'case_failed'     => $failed,
                  'case_successful' => $successful
               ];
            }

            $arrSummaryTestCase [] = [
               "suite"           => $names[1],
               "totalTestSuites" => $ttests,
               "classes"         => $total_class_test_x_suites,
               'failed'          => $failed,
               'success'         => $successful
            ];
            $ttests++;

            $names = explode("\\", $test_case);
            $html_report .= self::template_report_html()->render('body_report', [
               'names'   => $names,
               'i'       => $i,
               'reports' => $reports
            ]);
         }
      }

      $totalSummaryXSuite = self::summaryXsuites($arrSummaryTestCase);

      // render the summary for each suite
      foreach ($totalSummaryXSuite as $suite => $suiteSummarySuite)
      {
         $cards_summary_suites .= self::template_report_html()->render('summary_suite', [
            'suite'           => $suite,
            'totalTestSuites' => $suiteSummarySuite['totalTestSuites'],
            'class'           => $suiteSummarySuite['class'],
            'fail'            => $suiteSummarySuite['fail'],
            'success'         => $suiteSummarySuite['success']
         ]);
      }

      foreach ($namesSuitesMenu as $h => $item)
      {
         if ($h > 0 && $namesSuitesMenu[$h - 1] == $item)
         {
            $menu_items .= '';
         }
         else
         {
            $is_failed = self::is_failed($item, $total_cases_failed);
            $badge = self::get_badge($item, $total_cases_failed, $total_cases_successful);
            $fatal_error = self::get_cases_with_fatal_err($item, $tests_fatal_error);
            $type_fail = self::get_type_fail($item, $tests_type_fail);

            $menu_items .= self::template_report_html()->render('menu_items', [
               'item'               => $item,
               'is_failed'          => $is_failed,
               'namesSuitessubmenu' => $namesSuitessubmenu,
               'badge'              => $badge,
               'fatal_error'        => $fatal_error,
               'type_fail'          => $type_fail
            ]);
         }
      }

      if (count($total_cases_failed) >= 1)
      {
         $failed_cases = count($total_cases_failed);

         $failed_Summ = self::template_report_html()->render('failed_summary', ['total_cases_failed' => $total_cases_failed]);
      }

      if (count($total_cases_successful) >= 1)
      {
         $successful_case = count($total_cases_successful);

         $succ_Summ = self::template_report_html()->render('success_summary', ['total_cases_successful' => $total_cases_successful]);
      }

      $render = self::template_report_html()->render('content_report', [
         'total_suites'         => $total_suites,
         'total_cases'          => $total_cases,
         'failed_cases'         => $failed_cases,
         'successful_case'      => $successful_case,
         'html_report'          => $html_report,
         'test_time'            => $this->execution_time,
         'total_tests'          => $total_tests,
         'total_successful'     => $total_successful,
         'total_failed'         => $total_failed,
         'total_asserts'        => $total_asserts,
         'failed_Summ'          => $failed_Summ,
         'succ_Summ'            => $succ_Summ,
         'menu_items'           => $menu_items,
         'cards_summary_suites' => $cards_summary_suites
      ]);

      if ($path == '.'. DIRECTORY_SEPARATOR)
      {
         $path = 'test_report.html';
      }

      file_put_contents($path, $render);
   }

   public function is_failed($item, $total_cases_failed)
   {
      $faildSuite = false;
      foreach ($total_cases_failed as $suiteFaild)
      {
         $suites = explode("\\", $suiteFaild["case"]);
         if (array_search($item, $suites))
         {
            $faildSuite = true;
            break;
         }
      }

      return $faildSuite;
   }

   public function get_reports()
   {
      return $this->reports;
   }

   public function after_each_test($callback)
   {
      $this->after_each_test_function = $callback;
   }

   public function get_summary_report($total_suites, $total_cases, $total_tests, $total_asserts, $total_failed, $total_successful, $total_cases_failed, $total_cases_successful)
   {
      /**
       * render tables summary
       * @param String $row_separator, print "+--------------+--------------+--------------+" to table total Summary
       * @param String @$row_headers, header table total Summary
       * @param String $row_cells, data table total Summary (body)
       * @param int $gap_x, space on the x axis
       * @param String $joins, point joining both axes
       * @param String $axi_x, character for the x axis
       * @param String $axi_y, character for the y axis
       * @param String $column_width, default column width that will increase if any cell data is greater than this
       * @param Array $tableSummary, array with table data
       */

      $gap_x = 1;
      $joins = '+';
      $axi_x = '-';
      $axi_y = '|';
      $column_width = 10;

      //array with totals (suites,cases,tests)
      $tableSummary1 = [
         'Total suites'        => $total_suites,
         'Total test classes'  => $total_cases,
         'Total tests'         => $total_tests
      ];

      //array with Asserts totals
      $tableSummary2 = [
         'Asserts successful'  => $total_successful,
         'Asserts failed'      => $total_failed,
         'Total asserts'       => $total_asserts
      ];

      //generate table totals summary (suites,cases,tests)
      $summary1 = self::generate_table_summary_totals($column_width, $tableSummary1, $gap_x, $joins, $axi_x, $axi_y);

      //generate table totals asserts summary
      $summary2 = self::generate_table_summary_totals($column_width, $tableSummary2, $gap_x, $joins, $axi_x, $axi_y);

      echo PHP_EOL;

      echo 'Summary reports --> total time: ' . $this->execution_time . ' μs' . PHP_EOL . PHP_EOL;

      //render table totals summary (suites,cases,tests)
      foreach ($summary1 as $table1)
      {
         echo $table1 . PHP_EOL;
      }

      echo PHP_EOL;

      //render table totals asserts summary
      foreach ($summary2 as $table2)
      {
         echo $table2 . PHP_EOL;
      }

      echo PHP_EOL;
      echo PHP_EOL;

      //render table failed cases summary
      if (count($total_cases_failed) >= 1)
      {
         $failed_cases = count($total_cases_failed);

         echo 'Failed Summary: Cases failed (' . $failed_cases . ')' . PHP_EOL;

         $cases_failed_head = ['Suite', 'Class', 'Failed', 'Successful'];

         //generate table failed cases summary
         $summary_cases_failed = self::generate_table_cases_summary(5, $cases_failed_head, $total_cases_failed, $gap_x, $joins, $axi_x, $axi_y);

         // render table cases failded summary
         foreach ($summary_cases_failed as $cases_failed)
         {
            echo $cases_failed;
         }

         echo PHP_EOL;
      }
      else
      {
         $failed_cases = 0;

         echo 'Cases failed (' . $failed_cases . ')' . PHP_EOL;
      }

      echo PHP_EOL;

      //render table successful cases summary
      if (count($total_cases_successful) >= 1)
      {
         $successful_case = count($total_cases_successful);

         echo 'Successful Summary: Cases successful (' . $successful_case . ')' . PHP_EOL;

         $cases_success_head = ['Suite', 'Class', 'Successful'];

         //generate table success cases summary
         $summary_cases_success = self::generate_table_cases_summary(5, $cases_success_head, $total_cases_successful, $gap_x, $joins, $axi_x, $axi_y);

         // render table cases success summary
         foreach ($summary_cases_success as $cases_success)
         {
            echo $cases_success;
         }

         echo PHP_EOL;
      }
      else
      {
         $successful_case = 0;
         echo 'Cases Successful (' . $successful_case . ')' . PHP_EOL;
      }
   }

   public function generate_table_summary_totals($column_width, $tableSummary, $gap_x, $joins, $axi_x, $axi_y)
   {
      /**
       * generate summary table of totals
       */
      $row_separator = '';
      $row_headers = '';
      $row_cells = '';

      foreach ($tableSummary as $head => $row)
      {
         $length = strlen($head);

         if ($length > $column_width)
         {
            $column_width = $length;
         }

         /* separator character is created line by line - adds spaces to the string and adds the number
          of characters of max width - space between the characters and finally the alignment that
          the string will have*/
         $row_headers .= $axi_y . str_pad($head, ($gap_x * 2) + $column_width, ' ', STR_PAD_BOTH) . ' ';
         $row_cells .= $axi_y . str_pad($row, ($gap_x * 2) + $column_width, ' ', STR_PAD_LEFT) . ' ';

         //separator is created with the characters "+" and "-" the latter is repeated to form a line
         $row_separator .= $joins . str_repeat($axi_x, ($gap_x * 2) + $column_width) . $axi_x;
      }

      /* I end each line with the character "|" to close the table and the character "+" to join the
      intercepts of the x y axes */
      $row_headers .= $axi_y;

      $row_cells .= $axi_y;

      $row_separator .= $joins;

      return $dataTable = [
         '$row_separator1' => $row_separator,
         'row_headers'     => $row_headers,
         '$row_separator2' => $row_separator,
         '$row_cells'      => $row_cells,
         '$row_separator3' => $row_separator
      ];
   }

   public function generate_table_cases_summary($column_width, $arr_head, $arr_cases, $gap_x, $joins, $axi_x, $axi_y)
   {
      /**
       * generate summary table of successful summary and faild summary
       *
       */

      $row_separator_cases = '';
      $row_headers = '';
      $row_cells = '';

      //make the width of the column ('Suite' , 'Class') adapted to the content
      for ($i = 0; $i < count($arr_cases); $i++)
      {
         $name_cas = explode("\\", $arr_cases[$i]['case']);
         $len1 = strlen($name_cas[2]);
         $len2 = strlen($name_cas[1]);

         if ($len1 > $column_width)
         {
            $column_width = $len1;
         }

         if ($len2 > $len1)
         {
            $column_width = $len2;
         }
      }

      //create the table header with its separators
      foreach ($arr_head as $head)
      {
         /* separator character is created line by line - adds spaces to the string and adds the number
            of characters of max width - space between the characters and finally the alignment that
            the string will have*/
         $row_headers .= $axi_y . ' ' . str_pad($head, ($gap_x * 2) + $column_width, ' ', STR_PAD_BOTH) . ' ';
         $row_separator_cases .= $joins . $axi_x . str_repeat($axi_x, ($gap_x * 2) + $column_width) . $axi_x;
      }

      $row_headers .= $axi_y;

      foreach ($arr_cases as $total_case)
      {
         $names_failed = explode("\\", $total_case['case']);

         /* separator character is created line by line - adds spaces to the string and adds the number
            of characters of max width - space between the characters and finally the alignment that
            the string will have*/
         $row_cells .= $axi_y . ' ' . str_pad($names_failed[1], ($gap_x * 2) + $column_width, ' ', STR_PAD_RIGHT) . ' ' .
            $axi_y . ' ' . str_pad($names_failed[2], ($gap_x * 2) + $column_width, ' ', STR_PAD_RIGHT) . ' ';

         // FIXME: to show a table should depend only on the data, the content to display shouldnt depend on the number of items, so the data array shouldn't have keys, just data.
         if (count($arr_head) == 4)
         {
            $row_cells .= $axi_y . ' ' . str_pad($total_case['case_failed'], ($gap_x * 2) + $column_width, ' ', STR_PAD_LEFT) . ' ' .
               $axi_y . ' ' . str_pad($total_case['case_successful'], ($gap_x * 2) + $column_width, ' ', STR_PAD_LEFT) . ' ' . $axi_y . PHP_EOL;
         }
         else
         {
            $row_cells .= $axi_y . ' ' . str_pad($total_case['case_successful'], ($gap_x * 2) + $column_width, ' ', STR_PAD_LEFT) . ' ' . $axi_y . PHP_EOL;
         }
      }

      $row_separator_cases .= $joins;

      return $summary_case = [
         'row_separator_cases1' => $row_separator_cases . PHP_EOL,
         'row_headers_failed'   => $row_headers . PHP_EOL,
         'row_separator_cases2' => $row_separator_cases . PHP_EOL,
         'row_cells_failed'     => $row_cells,
         'row_separator_cases3' => $row_separator_cases . PHP_EOL
      ];
   }

   public function get_badge($item, $total_cases_failed, $total_cases_successful)
   {
      $case_failed = 0;
      $case_successfull = 0;
      $total_cases = 0;
      $badge = [];

      foreach ($total_cases_failed as $suiteFaild)
      {
         $suites = explode("\\", $suiteFaild["case"]);
         if (array_search($item, $suites))
         {
            $case_failed += $suiteFaild['case_failed'];
            $case_successfull += $suiteFaild['case_successful'];
         }
      }

      foreach ($total_cases_successful as $suiteSuccess)
      {
         $suites = explode("\\", $suiteSuccess["case"]);
         if (array_search($item, $suites))
         {
            $case_successfull += $suiteSuccess['case_successful'];
         }
      }

      $total_cases = $case_successfull + $case_failed;

      $badge = [
         'case_successfull' => $case_successfull,
         'case_failed'      => $case_failed,
         'total_cases'      => $total_cases
      ];

      return $badge;
   }

   public static function template_report_html()
   {
      $path = __DIR__ . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'views'. DIRECTORY_SEPARATOR .'templates';
      return new \League\Plates\Engine($path);
   }

   public function summaryXsuites($arrSummaryTestCase)
   {
      /** create the summary for each suite */
      $totalTests = 0;
      $totalClass = 0;
      $totalsuccess = 0;
      $totalfail = 0;
      $arr = [];

      for ($i=0; $i < count($arrSummaryTestCase); $i++)
      {
         $a = $i - 1;
         if ($i > 0)
         {
            if ($arrSummaryTestCase[$i]["suite"] === $arrSummaryTestCase[$a]["suite"]) {
               $totalTests = $arrSummaryTestCase[$i]["totalTestSuites"];
               $totalClass = $arrSummaryTestCase[$i]["classes"];
               $totalsuccess += $arrSummaryTestCase[$i]["success"];
               $totalfail += $arrSummaryTestCase[$i]["failed"];
            }
            else
            {
               $totalTests = $arrSummaryTestCase[$i]["totalTestSuites"];
               $totalClass = $arrSummaryTestCase[$i]["classes"];
               $totalsuccess = $arrSummaryTestCase[$i]["success"];
               $totalfail = $arrSummaryTestCase[$i]["failed"];
            }
         }
         else
         {
            $totalTests = $arrSummaryTestCase[$i]["totalTestSuites"];
            $totalClass = $arrSummaryTestCase[$i]["classes"];
            $totalsuccess = $arrSummaryTestCase[$i]["success"];
            $totalfail = $arrSummaryTestCase[$i]["failed"];
         }

         $arr[$arrSummaryTestCase[$i]["suite"]] = [
            "totalTestSuites" => $totalTests,
            "class" => $totalClass,
            "success" => $totalsuccess,
            "fail" => $totalfail
         ];
      }
      return $arr;
   }

   public function get_cases_with_fatal_err($item, $tests_fatal_error)
   {
      $fatal_error_php = '';
      foreach ($tests_fatal_error as $fatal)
      {
         $suite_error = explode("\\", $fatal["case"]);
         if (array_search($item, $suite_error))
         {
            $fatal_error_php = $suite_error[2];
         }
      }
      return $fatal_error_php;
   }

   public function get_type_fail($item, $tests_type_fail)
   {
      $type_fail = '';
      foreach ($tests_type_fail as $fail)
      {
         $suite_error = explode("\\", $fail["case"]);
         if (array_search($item, $suite_error))
         {
            $type_fail = $suite_error[2];
         }
      }
      return $type_fail;
   }
}