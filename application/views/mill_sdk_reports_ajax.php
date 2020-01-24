<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php print base_url();?>/assets/js/pace.min.js"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!-- Records -->
<?php $this->load->view('users/headermenu');?>
<div id="members_body" class="row">
  <?php $this->load->view('mill_sdk_reports_body');?>
</div>

<script type="text/javascript">
  function members_body(rows, pageno, sortby, sort_order) {  
  $("#gifloader").show();  
  var keywords = $("#keywords").val();
  var id = $("#id").val();
  var salon_id = $("#salon_id").val();
  var salon_name = $("#salon_name").val();
  var session_status = $("#session_status").val();
  var appointment_status = $("#appointment_status").val();
  var created_date = $("#created_date").val();

  var qStr = {"rows":rows, "pageno":pageno, "sortby":sortby, "sort_order":sort_order, "keywords": keywords,"id":id,"salon_id":salon_id,
  "salon_name":salon_name,"session_status":session_status,"appointment_status":appointment_status,"created_date":created_date};
  
  $.post("<?php echo base_url();?>MillSdkReports/members_body", qStr, function (data) {
    $("#gifloader").hide();  
    $("#members_body").html(data);
    //$('#pager-show').html($("#pager").html());
  });
}
function refresh_members() {
  $("input[name=keywords]").val("");
  $("input[name=id]").val("");
  $("input[name=salon_id]").val("");
  $("input[name=salon_name]").val("");
  $("input[name=session_status]").val("");
  $("input[name=appointment_status]").val("");
  $("input[name=created_date]").val("");
  members_body(10, 1, 'id', 'desc');
}

function member_body(id) {
  qStr = {"id":id}
  $.post("<?php echo base_url();?>MillSdkReports/members_body",qStr,function (data) {
    $("#member_body").html(data);
    highlight(id);
  });
}
</script>
