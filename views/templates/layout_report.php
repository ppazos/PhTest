<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Test summary</title>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.css"
    integrity="sha512-gOfBez3ehpchNxj4TfBZfX1MDLKLRif67tFJNLQSpF13lXM1t9ffMNCbZfZNBfcN2/SaWvOf+7CvIHtQ0Nci2A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css"
    integrity="sha512-Mk4n0eeNdGiUHlWvZRybiowkcu+Fo2t4XwsJyyDghASMeFGH6yUXcdDI3CKq12an5J8fq4EFzRVRdbjerO3RmQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
    integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
      @media (min-width: 500px){
        .text-truncate
        {
          max-width: 72px;
        }
      }
      @media (min-width: 992px) {
        .text-truncate
        {
          max-width: 102px;
        }
      }
      .table.faild_table > tbody > tr {
        cursor: pointer;
      }
    </style>
</head>
<body id="page-top">
  <!-- Page Wrapper -->
  <div id="wrapper">
    <?=$this->section('content')?>
    <!-- End of Main Content -->
  </div>
  <!-- End of Content Wrapper -->
  <!-- Bootstrap core JavaScript-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
    integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.2.1/js/bootstrap.bundle.js"
    integrity="sha512-4WQnCRyZ0CILKrMrO1P40yJrObxaNBOuImuSCFRV47/CWYh3ISyVPmqZnhiZ4OmhHstEj+QaoMDpQo5SnOXDAw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- Core plugin JavaScript-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"
    integrity="sha512-0QbL0ph8Tc8g5bLhfVzSqxe9GERORsKhIn1IrpxDAgUsbBGz/V7iSav2zzW325XGd1OMLdL4UiqRJj702IeqnQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.js"
    integrity="sha512-M82XdXhPLLSki+Ld1MsafNzOgHQB3txZr8+SQlGXSwn6veeqtYhPLbQeAfk9Y/Q9/gXcfa1jWT4YYUeoei6Uow=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js"
    integrity="sha512-+QnjQxxaOpoJ+AAeNgvVatHiUWEDbvHja9l46BHhmzvP0blLTXC4LsvwDVeNhGgqqGQYBQLFhdKFyjzPX6HGmw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js"
    integrity="sha512-+QnjQxxaOpoJ+AAeNgvVatHiUWEDbvHja9l46BHhmzvP0blLTXC4LsvwDVeNhGgqqGQYBQLFhdKFyjzPX6HGmw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  <script>
    // TODO: add a minimized version of the JS
    $("li.nav-item").on("click", function ()
    {
      var id_li = $("a", this).attr("id");
      var class_card = 'card_' + id_li;

      if (id_li === 'dashboard')
      {
        $('#cardSummaryTables').show();
        $('#headCardSummary').show();
        $(this).addClass("active");
        $('#title_suite').hide();
        $('#Card_suites').hide();

        if ($('li > div').hasClass('show'))
        {
          $('li > div').collapse('hide');
          console.log($('li > div'));
        }
      }
      else if (id_li === class_card.substring(5))
      {
        $('#Card_suites').show();
        $('#title_suite').show();
        $('#title_suite').html(id_li);
        $('#Card_suites').find('.' + class_card).show();
        $(this).addClass("active");

        $('.suites_test').not('.' + class_card).hide();
        $('#headCardSummary').hide();
        $('#cardSummaryTables').hide();
      }

      $('.nav-item').not(this).removeClass("active");
    });

    $(".collapse-item").on("click", function ()
    {
      var id_test_case = $(this).attr("id");
      var card_test = 'card_' + id_test_case;

      var id_li_suite_this = $(this).parent().attr("id");
      var this_suite = id_li_suite_this.substring(9);

      if (id_test_case === card_test.substring(5))
      {
        $(this).addClass("active");
        $('#title_suite').show();
        $('#title_suite').html(this_suite);
        $('#Card_suites').show();
        $('.row_testcases').show();
        $('.suites_test').show();

        $('.row_testcases').not('#card_' + id_test_case).hide();
        $('.card_summary_suites').hide();
        $('#headCardSummary').hide();
        $('#cardSummaryTables').hide();
      }

      $('.collapse-item').not(this).removeClass("active");

      $("#" + this_suite).on("click", function ()
      {
        $('#Card_suites').find('.row_testcases').show();
      });
    });

    //when you click on each suite on the menu
    $('.nav-link').on("click", function ()
    {
      var id_li = $(this).attr("id");
      var class_card = 'card_' + id_li;
      $('#Card_suites').find('.row_testcases').show();

      AllSuite(id_li , class_card);

      $('.nav-item').not(this).removeClass("active");
      $('.collapse-item').removeClass("active");
    });

    function AllSuite(id_li , class_card)
    {
      if (id_li === 'dashboard')
      {
        $('#cardSummaryTables').show();
        $('#headCardSummary').show();

        $(this).addClass("active");
        $('#title_suite').hide();
        $('#Card_suites').hide();

        if ($('li > div').hasClass('show'))
        {
          $('li > div').collapse('hide');
        }
      }
      else if (id_li === class_card.substring(5))
      {
        $(this).addClass("active");
        $('#Card_suites').show();
        $('#title_suite').show();
        $('#title_suite').html(id_li);
        $('#card_summary_' + id_li).show();
        $('.suites_test').show();
        $('#Card_suites').find('.' + class_card).show();


        $('.suites_test').not('.' + class_card).hide();
        $('.card_summary_suites').not('#card_summary_' + id_li).hide();
        $('#headCardSummary').hide();
        $('#cardSummaryTables').hide();
      }
    }

    $('table.faild_table tbody tr').on("click", function () {
      let id = $(this).attr('id');
      let str = id;
      let arr = str.split("\\");

      let id_li = arr[2];
      let class_card = 'card_' + id_li;

      $('#title_suite').show();
      $('#title_suite').html(arr[1]);

      $('#Card_suites').show();
      $('.suites_test').show();
      $('#Card_suites').find('.' + class_card).show();

      $('.row_testcases').hide();
      $('#' + class_card).show();

      $('.card_summary_suites').hide();
      $('#headCardSummary').hide();
      $('#cardSummaryTables').hide();
    });
  </script>
</body>
</html>