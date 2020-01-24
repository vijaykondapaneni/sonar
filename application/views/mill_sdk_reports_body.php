
<script type="text/javascript">
$(function() {
      $( "#created_date" ).datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
      });
                   
  });
</script>    
<style type="text/css">
 .pace {
  -webkit-pointer-events: none;
  pointer-events: none;

  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;

  z-index: 2000;
  position: fixed;
  margin: auto;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  height: 5px;
  width: 200px;
  background: #fff;
  border: 1px solid #29d;

  overflow: hidden;
}

.pace .pace-progress {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  -ms-box-sizing: border-box;
  -o-box-sizing: border-box;
  box-sizing: border-box;

  -webkit-transform: translate3d(0, 0, 0);
  -moz-transform: translate3d(0, 0, 0);
  -ms-transform: translate3d(0, 0, 0);
  -o-transform: translate3d(0, 0, 0);
  transform: translate3d(0, 0, 0);

  max-width: 200px;
  position: fixed;
  z-index: 2000;
  display: block;
  position: absolute;
  top: 0;
  right: 100%;
  height: 100%;
  width: 100%;
  background: #29d;
}

.pace.pace-inactive {
  display: none;
}
</style><?php
//print_r($params);
$post_nums = array(10, 20, 50, 100, 200, 500);
$rows = $params['rows'];
$pageno = $params['pageno'];
$sortby = $params['sortby'];
$sort_order = $params['sort_order'];
$tRecords = $params['tRecords'];
$total_pages = ceil($tRecords/$rows);
$keywords = (isset($params['keywords'])) ? $params['keywords'] : "" ;
$id = (isset($params['id'])) ? $params['id'] : "" ;
$salon_id = (isset($params['salon_id'])) ? $params['salon_id'] : "" ;
$salon_name = (isset($params['salon_name'])) ? $params['salon_name'] : "" ;
$session_status = (isset($params['session_status'])) ? $params['session_status'] : "" ;
$appointment_status = (isset($params['appointment_status'])) ? $params['appointment_status'] : "" ;
$created_date = (isset($params['created_date'])) ? $params['created_date'] : "" ;
?>
<?php
  $signature = '';
  $timestamp = '';
  if(isset($_GET['signature']) && isset($_GET['timestamp'])){
    $signature = $_GET['signature'];
    $timestamp = $_GET['timestamp'];
  }  
?>
<style>
.modal-dialog {width:100% !important; max-width:600px !important; left:auto !important;word-break: break-all !important;}
</style>
<div class="container">
  <div class="cal-main-title">
    <h1><?php print ("Mill SDK Reports") . ' (' . $tRecords . ')';?>
      <a href="<?php echo base_url();?>index.php/MillSdkReports/Individualsalon?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>" class="pull-right">Current Status Of SDK</a>
    </h1>
  </div>
</div>
 
<div class="container">
  <div class="pull-left col_pad2">
    <form class="form-inline">
      <input type="hidden" id="type" name="type"  class="form-control ">
      <div class="form-group">
        <input class="form-control input-sm" type="text" id="keywords" name="keywords" placeholder="keywords" value="<?php print $keywords;?>" >
      </div>
      <div class="form-group">
        <a class="btn btn-success btn-sm" id="search" onclick="members_body('<?php print $rows;?>', '<?php print $pageno?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')" title="search">
          <span class="glyphicon glyphicon-search"></span>
        </a>
        <a class="btn btn-default btn-sm" onclick="refresh_members()" title="Reset">
          <span class="glyphicon glyphicon-refresh"></span>
        </a>
      </div>
    </form>
  </div>
  <div class="pull-right col_pad2">
    <form class="form-inline">
      <div class="form-group">
        <div id="pager-show">
          <?php
          if($records) {
            ?>
            <div class="form-group">
              <select class="form-control input-sm" name="rows" onchange="members_body(this.value, '1', '<?php print $sortby;?>', '<?php print $sort_order;?>')" >
                <option value="10" <?php if ($rows == 10) print 'selected="selected"';?> >
                  10 <?php print _("Records");?>
                </option>
                <option value="20" <?php if ($rows == 20) print 'selected="selected"';?> >
                  20 <?php print _("Records");?>
                </option>
                <option value="50" <?php if ($rows == 50) print 'selected="selected"';?> >
                  50 <?php print _("Records");?>
                </option>
                <option value="100" <?php if ($rows == 100) print 'selected="selected"';?> >
                  100 <?php print _("Records");?>
                </option>
                <option value="10000000000" <?php if ($rows == '10000000000') print 'selected="selected"';?> >
                  <?php print _("All");?>
                </option>
              </select>
            </div>
            <?php
            if ($pageno == 1) {
              ?>
              <a class="btn btn-primary  btn-sm disabled"><span class="fa fa-chevron-left"></span><span class="fa fa-chevron-left"></span></a>
              <a class="btn btn-primary  btn-sm disabled"><span class="fa fa-chevron-left"></span></a>
              <?php
            }
            else {
              ?>
              <a class="btn btn-primary  btn-sm" onclick="members_body('<?php print $rows;?>', '1', '<?php print $sortby;?>', '<?php print $sort_order;?>')"><span class="fa fa-chevron-left"></span><span class="fa fa-chevron-left"></span></a>
              <a class="btn btn-primary  btn-sm" onclick="members_body('<?php print $rows;?>', '<?php print $pageno-1;?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')" ><span class="fa fa-chevron-left"></span></a>
              <?php
            }
            ?>
            <a class="btn btn-primary  btn-sm">
              <?php print $pageno;?>/<?php print $total_pages;?>
            </a>
            <?php
            if ($pageno == $total_pages) {
              ?>
              <a class="btn btn-primary  btn-sm disabled"><span class="fa fa-chevron-right"></span></a>
              <a class="btn btn-primary  btn-sm disabled"><span class="fa fa-chevron-right"><span class="fa fa-chevron-right"></span></span></a>
              <?php
            }
            else {
              ?>
              <a class="btn btn-primary  btn-sm" onclick="members_body('<?php print $rows;?>', '<?php print $pageno+1;?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')"><span class="fa fa-chevron-right"></span></a>
              <a class="btn btn-primary  btn-sm" onclick="members_body('<?php print $rows;?>', '<?php print $total_pages;?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')" ><span class="fa fa-chevron-right"></span><span class="fa fa-chevron-right"></span></a>
              <?php
            }
          }
          ?>
        </div>
      </div>

    </form>
  </div>
</div>
<div class="container table-responsive" >
  <table class="table table-bordered table-hover">
    <thead>
      <tr class="bg-primary">
        <th witdh="1%" class="text-center">ID</th>
        <th witdh="5%" class="text-center">Salon Id</th>
        <th class="">Salon Name</th>
        <th class="">Session Status</th>
        <th class="">Appointment Status</th>
        <th class="text-center">Date</th>
        <th class="">Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- filters -->
      <tr class="text-center">
        <td witdh="1%" >#</td>
       
        <td>
          <input type="text" id="salon_id" name="salon_id" placeholder="Salon Id" class="form-control" value="<?php print $salon_id;?>" >
        </td>
        <td >
          <input type="text" id="salon_name" name="salon_name" placeholder="Salon Name" class="form-control" value="<?php print $salon_name;?>">
        </td>
        <td>
          <select class="form-control" name="session_status" id="session_status" onchange="members_body('<?php print $rows;?>', '<?php print $pageno?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')">
            <option value="0">All</option>
            <option value="1" <?php ($session_status == 1)? print 'selected="selected"': print '';?> >Success</option>
            <option value="2" <?php ($session_status == 2)? print 'selected="selected"': print '';?> >Fail</option>
          </select>
        </td>
        <td>
          <select class="form-control" name="appointment_status" id="appointment_status" onchange="members_body('<?php print $rows;?>', '<?php print $pageno?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')">
            <option value="0">All</option>
            <option value="1" <?php ($appointment_status == 1)? print 'selected="selected"': print '';?> >Success</option>
            <option value="2" <?php ($appointment_status == 2)? print 'selected="selected"': print '';?> >Fail</option>
          </select>
        </td>
        <td class="text-center">
          <input type="text"  id="created_date" name="created_date" Placeholder="Date" autocomplete="off" value="<?php print $created_date;?>"  class="form-control" />
          <input type = "hidden" id="created_date_reports" name="created_date_reports" />
        </td>
        <td>
          <a class="btn btn-success btn-sm" onclick="members_body('<?php print $rows;?>', '<?php print $pageno?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')" title="search">
            <span class="glyphicon glyphicon-search"></span>
          </a>
          <a class="btn btn-default btn-sm" onclick="refresh_members()" title="Reset">
            <span class="glyphicon glyphicon-refresh"></span>
          </a>
        </td>
      </tr>
      <!-- Records -->
      <?php
      if (isset($records) and $records!=''){
        $i = ($pageno-1)*$rows+1;
        foreach ($records as $key => $value) {

        if($value['session_status']==0){
         $session_status = 'Success';
         $session_status = $session_status.'&nbsp;&nbsp;'.'<img src="'.base_url().'/assets/images/success.png" height="20" width="20">';
        }else{
           $session_status = 'Fail';
           $session_status = $session_status.'&nbsp;&nbsp;'.'<img src="'.base_url().'/assets/images/error.png" height="20" width="20">';
        }
        if($value['appointment_status']==0){
           $appointment_status = 'Success';
           $appointment_status = $appointment_status.'&nbsp;&nbsp;'.'<img src="'.base_url().'/assets/images/success.png" height="20" width="20">';
        }else{
           $appointment_status = 'Fail';
           $appointment_status = $appointment_status.'&nbsp;&nbsp;'.'<img src="'.base_url().'/assets/images/error.png" height="20" width="20">';
        }
          
        ?>
        <tr class="table-row" id="row_<?php print $key;?>" >
          <td class="text-center"  width="1%"><?php print $i++;?></td>
          
          <td>
            <?php print $value['salon_id'];?>
          </td>
          <td>
            <?php print $value['salon_name'];?>
          </td>
          <td>
            <?php print $session_status;?>
          </td>
          <td><?php print $appointment_status;?></td>
          <td><?php print $value['created_date'];?></td>
          <td class="action_calbtns">
            <?php if(($value['session_status']==1) || ($value['appointment_status']==1)) {?>
        <div class="btn-group">
          <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          View Errors <span class="caret"></span>
          </button> 
          <ul class="dropdown-menu">
          <?php if($value['session_status']==1) { ?>
          <li><a href="#myModalSession<?php echo $value['id'];?>" data-toggle="modal">Session</a></li>
          <?php } ?>
          <?php if(($value['session_status']==1) && ($value['appointment_status']==1)) {?>
          <li class="divider"></li>
          <?php } ?>
          <?php if($value['appointment_status']==1) { ?>
          <li><a href="#myModalAppointment<?php echo $value['id'];?>" data-toggle="modal">Appointment</a></li>
          <?php } ?>
          </ul>
        </div>
        <?php if($value['session_status']==1) { ?>
        <div class='clear'></div>
        <!-- Session Error Modal Popup -->
        <div class="modal fade" id="myModalSession<?php echo $value['id'];?>" role="dialog">
          <div class="modal-dialog">
            <!-- Modal content -->
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"> <?php print $value['salon_name'];?> - Session Error</h4>
              </div>
              <div class="modal-body">
                <?php echo $value['session_error']; ?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
           </div>
        </div>
        
        <!-- Session Error Modal Popup --> 
        <?php } ?>
        <?php if($value['appointment_status']==1) { ?>
        <div class='clear'></div>
        <!-- Appointment Error Modal Popup -->
        <div class="modal fade" id="myModalAppointment<?php echo $value['id'];?>" role="dialog">
          <div class="modal-dialog">
            <!-- Modal content -->
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"> <?php print $value['salon_name'];?> - Appointment Error</h4>
              </div>
              <div class="modal-body">
                <?php echo $value['appointment_error']; ?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
           </div>
        </div>
        <!-- Appointment Error Modal Popup -->
        <?php } ?>
            <?php } ?>
          </td>
        </tr>
        <?php
      }
    }
    else {
      ?>
      <tr><td colspan="9"><div class="alert alert-warning"><?php print _("No Records Found.");?></div></td></tr>
      <?php
    }
    ?>
    </tbody>
  </table>
</div>
<div class="container">
  <div class="pull-left col_pad2">
    <form class="form-inline">
      <input type="hidden" id="type" name="type"  class="form-control ">
      <div class="form-group">
        <input class="form-control input-sm" type="text" id="keywords" name="keywords" placeholder="keywords" value="<?php print $keywords;?>" >
      </div>
      <div class="form-group">
        <a class="btn btn-success btn-sm" id="search" onclick="members_body('<?php print $rows;?>', '<?php print $pageno?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')" title="search">
          <span class="glyphicon glyphicon-search"></span>
        </a>
        <a class="btn btn-default btn-sm" onclick="refresh_members()" title="Reset">
          <span class="glyphicon glyphicon-refresh"></span>
        </a>
      </div>
    </form>
  </div>
  <div class="pull-right col_pad2">
    <form class="form-inline">
      <div class="form-group">
        <div id="pager-show">
          <?php
          if($records) {
            ?>
            <div class="form-group">
              <select class="form-control input-sm" name="rows" onchange="members_body(this.value, '1', '<?php print $sortby;?>', '<?php print $sort_order;?>')" >
                <option value="10" <?php if ($rows == 10) print 'selected="selected"';?> >
                  10 <?php print _("Records");?>
                </option>
                <option value="20" <?php if ($rows == 20) print 'selected="selected"';?> >
                  20 <?php print _("Records");?>
                </option>
                <option value="50" <?php if ($rows == 50) print 'selected="selected"';?> >
                  50 <?php print _("Records");?>
                </option>
                <option value="100" <?php if ($rows == 100) print 'selected="selected"';?> >
                  100 <?php print _("Records");?>
                </option>
                <option value="10000000000" <?php if ($rows == '10000000000') print 'selected="selected"';?> >
                  <?php print _("All");?>
                </option>
              </select>
            </div>
            <?php
            if ($pageno == 1) {
              ?>
              <a class="btn btn-primary  btn-sm disabled"><span class="fa fa-chevron-left"></span><span class="fa fa-chevron-left"></span></a>
              <a class="btn btn-primary  btn-sm disabled"><span class="fa fa-chevron-left"></span></a>
              <?php
            }
            else {
              ?>
              <a class="btn btn-primary  btn-sm" onclick="members_body('<?php print $rows;?>', '1', '<?php print $sortby;?>', '<?php print $sort_order;?>')"><span class="fa fa-chevron-left"></span><span class="fa fa-chevron-left"></span></a>
              <a class="btn btn-primary  btn-sm" onclick="members_body('<?php print $rows;?>', '<?php print $pageno-1;?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')" ><span class="fa fa-chevron-left"></span></a>
              <?php
            }
            ?>
            <a class="btn btn-primary  btn-sm">
              <?php print $pageno;?>/<?php print $total_pages;?>
            </a>
            <?php
            if ($pageno == $total_pages) {
              ?>
              <a class="btn btn-primary  btn-sm disabled"><span class="fa fa-chevron-right"></span></a>
              <a class="btn btn-primary  btn-sm disabled"><span class="fa fa-chevron-right"><span class="fa fa-chevron-right"></span></span></a>
              <?php
            }
            else {
              ?>
              <a class="btn btn-primary  btn-sm" onclick="members_body('<?php print $rows;?>', '<?php print $pageno+1;?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')"><span class="fa fa-chevron-right"></span></a>
              <a class="btn btn-primary  btn-sm" onclick="members_body('<?php print $rows;?>', '<?php print $total_pages;?>', '<?php print $sortby;?>', '<?php print $sort_order;?>')" ><span class="fa fa-chevron-right"></span><span class="fa fa-chevron-right"></span></a>
              <?php
            }
          }
          ?>
        </div>
      </div>

    </form>
  </div>
</div>

<style type="text/css">
  .action_calbtns ul li a.btn_del {
    text-align: left;
    border-radius: 0px;
    border: 0px none;
  }
</style>
