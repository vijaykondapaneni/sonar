<script>
function goBack() {
    window.history.back();
}
</script>

<?php $this->load->view('users/headermenu');?>
<div class="container">
	<div class="row main">
		<div class="main-login main-center">
				<div class="form-group">
					<label for="name" class="cols-sm-2 control-label">Select Salon</label>
					<div class="cols-sm-10">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-user fa" aria-hidden="true"></i></span>
							<?php  //pa($allresults);
							//usort($allresults, 'sortByOrder');
							//pa($allresults);
							//asort($allresults);?>
							<select class="form-control" name="salon_id" id="salon_id">
							   <?php
                                  
							     foreach ($allresults as $key=> $value) { ?>
							      	<option value="<?php echo $value['salon_id'];?>"><?php echo $value['salon_name'];?></option>
							    <?php  } 
							   ?>
								
							</select>
						</div>
					</div>
				</div>
				<div class="form-group ">
					<input id="report_submit" class="btn btn-success" style="margin-top:1px;" name="report_submit" value="Get Status" type="button">
				</div>
		</div>
	</div>
	<div class="">
        <div class="table-responsive">
            <div id="loader1_invoices" style="display: none;text-align: center;"><img src="<?php echo base_url();?>loading_spinner.gif"></div>
            <div id="tbody_table1" style="padding: 15px;">
                            
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
 $('#report_submit').click(function(){
        
        var txt="";
        $("#loader1_invoices").show();
        $('#tbody_table1').html(txt);
        var salon_id =  $('#salon_id').val();
        qStr = {"salon_id":salon_id}
        $.post( "<?php echo base_url();?>MillSdkReports/getSalonStatus",qStr,function(data) {
        	$("#loader1_invoices").hide();
            if(data != 0 && data != 1){
                $('#tbody_table1').html(data);
            }else if(data == 1){ 
                $('#tbody_table1').html("Salon is not connecting to SDK");
            }else{
                $('#tbody_table1').html("Salon details not found");
            }
        }); 
        
        
    });
</script>
