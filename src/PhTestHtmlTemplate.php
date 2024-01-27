<?php

namespace CaboLabs\PhTest;

/**
 * this class renders the html when the tests are run.
 * @param String $total_suites
 * @param String $total_cases
 * @param String $failed_cases
 * @param String $successful_case
 * @param String $html_report
 * @param String $test_time
 * @param String $total_tests
 * @param String $total_successful
 * @param String $total_failed
 * @param String $total_asserts
 * @param String $failed_Summ
 * @param String $succ_Summ
 * @param String $menu_items
 */

class PhTestHtmlTemplate
{
  function Html_template($total_suites, $total_cases, $failed_cases, $successful_case, $html_report, $test_time, $total_tests, $total_successful, $total_failed, $total_asserts, $failed_Summ, $succ_Summ, $menu_items)
  {
    // NOTE: this path is relative to this file using __DIR__ so it should work when running from other cli's
    //       if the path is relative, it will be relative to the running script, if cli.php is on a different
    //       folder than this package folder, then it won't find the js file.
    $test_report_js = file_get_contents(__DIR__ .'/../assets/js/views/test_report_index.js');

    $content = <<< EOD
      <!DOCTYPE html>
      <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <meta name="description" content="">
            <meta name="author" content="">

            <title>Test summary</title>

            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.css" integrity="sha512-gOfBez3ehpchNxj4TfBZfX1MDLKLRif67tFJNLQSpF13lXM1t9ffMNCbZfZNBfcN2/SaWvOf+7CvIHtQ0Nci2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css" integrity="sha512-Mk4n0eeNdGiUHlWvZRybiowkcu+Fo2t4XwsJyyDghASMeFGH6yUXcdDI3CKq12an5J8fq4EFzRVRdbjerO3RmQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        </head>
        <body id="page-top">

          <!-- Page Wrapper -->
          <div id="wrapper">

            <!-- Sidebar -->
            <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

              <!-- Sidebar - Brand -->

              <a class="sidebar-brand d-flex align-items-center justify-content-center" href="test_report.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">PhTest</div>
              </a>

              <!-- Divider -->
              <hr class="sidebar-divider my-0">

              <!-- Nav Item - Dashboard -->
              <li class="nav-item active">
                <a id="dashboard" class="nav-link" href="#">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
                </a>
              </li>

              <!-- Divider -->
              <hr class="sidebar-divider">

              <!-- Heading -->
              <div class="sidebar-heading">
                Test suites
              </div>

              <!-- Nav Item -->
              $menu_items

            </ul>
            <!-- End of Sidebar -->

            <!-- Content Wrapper -->
            <div id="content-wrapper" class="d-flex flex-column">
              <!-- Main Content -->
              <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-1 pb-2">
                    <h1 class="h2 text-gray-800"></h1>
                  </div>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                  <h2 id="title_suite"></h2>

                  <!-- Content Row -->
                  <div id= "headCardSummary" class="row">
                    <!-- Total suites Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                      <div class="card border-left-primary shadow h-100 py-2">
                          <div class="card-body">
                              <div class="row no-gutters align-items-center">
                                  <div class="col mr-2">
                                      <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total suites</div>
                                      <div class="h5 mb-0 font-weight-bold text-gray-800">$total_suites</div>
                                  </div>
                                  <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                  </div>
                              </div>
                          </div>
                      </div>
                    </div>

                    <!-- Total tests cases Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                      <div class="card border-left-warning shadow h-100 py-2">
                          <div class="card-body">
                              <div class="row no-gutters align-items-center">
                                  <div class="col mr-2">
                                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Total tests cases</div>
                                      <div class="h5 mb-0 font-weight-bold text-gray-800">$total_cases</div>
                                  </div>
                                  <div class="col-auto">
                                    <i class="fas fa-project-diagram fa-2x text-gray-300"></i></i>
                                  </div>
                              </div>
                          </div>
                      </div>
                    </div>

                    <!-- Cases failed Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                      <div class="card border-left-danger shadow h-100 py-2">
                          <div class="card-body">
                              <div class="row no-gutters align-items-center">
                                  <div class="col mr-2">
                                      <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Cases failed
                                      </div>
                                      <div class="row no-gutters align-items-center">
                                          <div class="col-auto">
                                              <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$failed_cases</div>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="col-auto">
                                      <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                  </div>
                              </div>
                          </div>
                      </div>
                    </div>

                    <!-- Cases successful Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                      <div class="card border-left-success shadow h-100 py-2">
                          <div class="card-body">
                              <div class="row no-gutters align-items-center">
                                  <div class="col mr-2">
                                      <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Cases successful</div>
                                      <div class="h5 mb-0 font-weight-bold text-gray-800">$successful_case</div>
                                  </div>
                                  <div class="col-auto">
                                    <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                                  </div>
                              </div>
                          </div>
                      </div>
                    </div>
                  </div>

                  <div id="Card_suites">
                    $html_report
                  </div>

                  <div id="cardSummaryTables" class="row">
                    <div class="col-xl-12 col-lg-12">
                      <div class="card shadow mb-4">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                          <h6 class="m-0 font-weight-bold text-success">Total time:  $test_time μs</h6>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                          <br>
                          <div class="shadow p-3 mb-5 bg-body rounded">
                            <h6 class="m-0 font-weight-bold text-primary">Total Summary</h6><br>
                            <table class="table">
                              <thead>
                                <tr>
                                  <th scope="col">Total suites</th>
                                  <th scope="col">Total test classes</th>
                                  <th scope="col">Total tests</th>
                                  <th scope="col">Asserts successful</th>
                                  <th scope="col">Asserts failed</th>
                                  <th scope="col">Total asserts</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td class="text-right">$total_suites</td>
                                  <td class="text-right">$total_cases</td>
                                  <td class="text-right">$total_tests</td>
                                  <td class="text-right">$total_successful</td>
                                  <td class="text-right">$total_failed</td>
                                  <td class="text-right">$total_asserts</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>

                          <div class="shadow p-3 mb-5 bg-body rounded" id="table_failed_cases">
                            <h6 class="m-0 font-weight-bold text-primary">Failed Summary</h6><br>
                            <table class="table" data-id="failed_cases">
                              <thead>
                                <tr>
                                  <th scope="col">Suite</th>
                                  <th scope="col">Class</th>
                                  <th class="text-right" scope="col">Successful</th>
                                  <th class="text-right" scope="col">Failed</th>
                                </tr>
                              </thead>
                              <tbody>
                                $failed_Summ
                              </tbody>
                            </table>
                          </div>

                          <br>
                          <div class="shadow p-3 mb-5 bg-body rounded">
                            <h6 class="m-0 font-weight-bold text-primary">Successful Summary</h6><br>
                            <table class="table">
                              <thead>
                                <tr>
                                  <th scope="col">Suite</th>
                                  <th scope="col">Class</th>
                                  <th class="text-right" scope="col">Successful</th>
                                </tr>
                              </thead>
                              <tbody>
                                $succ_Summ
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
            <!-- End of Main Content -->

          </div>
          <!-- End of Content Wrapper -->

          <!-- Bootstrap core JavaScript-->
          <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.2.1/js/bootstrap.bundle.js" integrity="sha512-4WQnCRyZ0CILKrMrO1P40yJrObxaNBOuImuSCFRV47/CWYh3ISyVPmqZnhiZ4OmhHstEj+QaoMDpQo5SnOXDAw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

          <!-- Core plugin JavaScript-->
          <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js" integrity="sha512-0QbL0ph8Tc8g5bLhfVzSqxe9GERORsKhIn1IrpxDAgUsbBGz/V7iSav2zzW325XGd1OMLdL4UiqRJj702IeqnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.js" integrity="sha512-M82XdXhPLLSki+Ld1MsafNzOgHQB3txZr8+SQlGXSwn6veeqtYhPLbQeAfk9Y/Q9/gXcfa1jWT4YYUeoei6Uow==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js" integrity="sha512-+QnjQxxaOpoJ+AAeNgvVatHiUWEDbvHja9l46BHhmzvP0blLTXC4LsvwDVeNhGgqqGQYBQLFhdKFyjzPX6HGmw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js" integrity="sha512-+QnjQxxaOpoJ+AAeNgvVatHiUWEDbvHja9l46BHhmzvP0blLTXC4LsvwDVeNhGgqqGQYBQLFhdKFyjzPX6HGmw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

          <!-- Custom scripts for all pages-->
          <script>
            {$test_report_js}
          </script>
        </body>
      </html>
    EOD;

    return $content;
  }
}
