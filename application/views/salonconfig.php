<script type="text/javascript" src="<?php print base_url();?>/assets/js/pace.min.js"></script>
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
</style>

<?php defined('BASEPATH') OR exit('No direct script access allowed');
  $this->load->view('users/headermenu');
?>
    <!--EOC: Top header section-->

    <div id="body">
        <table class="table table-bordered table-striped table-condensed" id="salons-table" width="100%" border="1">
          <thead>
            <tr>
                <th>S.no</th>
                <th align="left">Salon Name</th>
                <th>RPCT</th>  
                <th align="left">Status</th>  
                <th style="text-align: center;">Actions</th>
            </tr>
          </thead>
          <tbody>
           <?php 
            $i=1;
            if(!empty($allsalons['mill_salons'])){

            foreach ($allsalons['mill_salons'] as $results) {
              $sdkdata = $this->Common_model->getMillSdkConfigDetailsBy($results['salon_id'])->row_array();
              $servicetypedb = $sdkdata['service_types'];
              $selectedArr = explode(",",$servicetypedb);
              $leaderboardtypedb = $sdkdata['leaderboard_type'];
              $leaderselectedArr = explode(",",$leaderboardtypedb);
              $staffservicetypedb = $sdkdata['staff_service_types'];
              $staffselectedArr = explode(",",$staffservicetypedb);
              $staffleaderboardtypedb = $sdkdata['staff_leaderboard_type'];
              $staffleaderselectedArr = explode(",",$staffleaderboardtypedb);
              if($sdkdata['status']==0){
                $statusdisplay = 'Active';
              }else{
                $statusdisplay = 'InActive';
              }
              $rpctdb = $sdkdata['rpct_type'];
              $rpctdetails = $this->Common_model->getRpctTypeBy($rpctdb);
             ?>
                <tr>
                    <td align="center"><?= $i++; ?></td>
                    <td align="left"><?= $results['salon_name'];?></td>
                    <td><?php echo $rpctdetails['name'];?> <small><?php echo $rpctdetails['description'];?></small></td>
                    <td align="left"><?= $statusdisplay;?></td>
                    <td align="center">
                      <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#services_<?php echo $results['salon_id']?>">Owner Config</button>
                      <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#staff_<?php echo $results['salon_id']?>">Staff  Config</button>
                    </td>
                    <?php
                    $signature = '';
                    $timestamp = '';
                    if(isset($_GET['signature']) && isset($_GET['timestamp'])){
                      $signature = $_GET['signature'];
                      $timestamp = $_GET['timestamp'];
                    }  
                    ?>

                    <form name="services_update" method="get" action="<?php echo base_url()?>SalonConfigrations/updateconfig">
                    <div id="services_<?php echo $results['salon_id']?>" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title"><?php echo $results['salon_name'];?></h4>
                          </div>
                          <div class="modal-body">
                            <div class="form-group">
                             Metrics
                             <select name="servicetype[]" id="servicetype_retail_<?php echo $results['salon_id'];?>" multiple="multiple" style="height: 250px!important" class="form-control">
                              <?php foreach($allservices as $key=>$value){?>
                                <option value="<?php echo $value['id'];?>"
                                <?php if(in_array($value['id'],$selectedArr)){?>selected="selected" <?php } ?> ><?php echo $value['name'];?></option>
                               <?php } ?>
                             </select>
                             </div>
                             RPCT Types
                             <div class="form-group">
                             <select name="rpcttype" class="form-control">
                              <?php foreach($allrpcts as $key=>$value){?>
                                <option value="<?php echo $value['id'];?>"
                                <?php if($value['id']==$rpctdb){?>selected="selected" <?php } ?> ><?php echo $value['name'];?>--<?php echo $value['description'];?></option>
                               <?php } ?>
                             </select>
                             </div>

                             Leader Board 
                             <div class="form-group">
                            <select name="leaderboardtype[]" multiple="multiple" style="height: 150px!important" class="form-control">
                              <?php foreach($allleaderboardtypes as $key=>$value){?>
                                <option value="<?php echo $value['id'];?>"
                                <?php if(in_array($value['id'],$leaderselectedArr)){?>selected="selected" <?php } ?> ><?php echo $value['name'];?></option>
                               <?php } ?>
                             </select>
                             </div>
                          </div>
                          <div class="modal-footer">
                            <input type="hidden" name="salon_id" value="<?php echo $results['salon_id'];?>">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <input type="hidden" name="signature" value="<?php echo $signature;?>">
                            <input type="hidden" name="timestamp" value="<?php echo $timestamp;?>">

                          </div>
                        </div>

                      </div>
                    </div>
                   </form>
                   <form name="staff_services_update" method="get" action="<?php echo base_url()?>SalonConfigrations/updatestaffconfig">
                    <div id="staff_<?php echo $results['salon_id']?>" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title"><?php echo $results['salon_name'];?></h4>
                          </div>
                          <div class="modal-body">
                            <div class="form-group">
                             Staff Metrics
                             <select name="staffservicetype[]"  multiple="multiple" style="height: 250px!important" class="form-control">
                              <?php foreach($allstaffservices as $key=>$value){?>
                                <option value="<?php echo $value['backend_name'];?>"
                                <?php if(in_array($value['backend_name'],$staffselectedArr)){?>selected="selected" <?php } ?> ><?php echo $value['name'];?></option>
                               <?php } ?>
                             </select>
                             </div>
                             Staff Leader Board 
                             <div class="form-group">
                            <select name="staffleaderboardtype[]" multiple="multiple" style="height: 150px!important" class="form-control">
                              <?php foreach($allstaffleaderboardtypes as $key=>$value){?>
                                <option value="<?php echo $value['id'];?>"
                                <?php if(in_array($value['id'],$staffleaderselectedArr)){?>selected="selected" <?php } ?> ><?php echo $value['name'];?></option>
                               <?php } ?>
                             </select>
                             </div>
                          </div>
                          <div class="modal-footer">
                            <input type="hidden" name="salon_id" value="<?php echo $results['salon_id'];?>">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <input type="hidden" name="signature" value="<?php echo $signature;?>">
                             <input type="hidden" name="timestamp" value="<?php echo $timestamp;?>">

                          </div>
                        </div>

                      </div>
                    </div>
                   </form> 
                </tr>
            <?php } } else{?>
             <tr>
               <td colspan="4">No Salons</td>
             </tr>
             <?php } ?>
          </tbody>   
        </table>
    </div>