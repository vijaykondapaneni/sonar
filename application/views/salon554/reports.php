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
<?php 
	//echo "<pre>";print_r($allresults);
$this->load->view('users/headermenu');
error_reporting(E_ERROR | E_PARSE);

	if($year_reports == date('Y')){
		$tot_mnths = date('n');
	}else{
		$tot_mnths = 12;
	}
?>
<div class="container">
  <div class="cal-main-title">
    <h1><?php echo $title?> <?php if($staff_name!=''){ echo "--"; echo "&nbsp;"; echo $staff_name; }?></h1>
    	<div class="pull-right"><button onclick="goBack()" class="btn btn-success">Back</button></div>
    </h4>
  </div>
</div>

<div class="container table-responsive" >
  <table class="table table-bordered table-hover">
    <thead>
      <tr class="bg-primary">
        <th witdh="1%" class="text-center"><?php echo $year_reports;?></th>
        <th>Jan</th>
        <th>Feb</th>
        <th>Mar</th>
        <th>Apr</th>
        <th>May</th>
        <th>Jun</th>
        <th>Jul</th>
        <th>Aug</th>
        <th>Sep</th>
        <th>Oct</th>
        <th>Nov</th>
        <th>Dec</th>
        <th>YTD Totals</th>
        <th>Monthly Average</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
	 <tr class="bg-primary">
        <td colspan="16" align="center"><b>CLIENTELE</b></td>
      </tr>
      <tr>
        <td witdh="1%" class="text-center">New Guests</td>
		<?php foreach($allresults['new_clients'] as $new_clients_val){
			echo "<th>".$new_clients_val."</th>";
		} 
		echo "<th>".$allresults['total_new_clients']."</th>";
		echo "<th>".number_format($allresults['total_new_clients']/$tot_mnths,2)."</th>";
		?>
        <th>New Guests</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Refferals</th>
			<?php foreach($allresults['referral_clients'] as $referral_clients_val){
				echo "<th>".$referral_clients_val."</th>";
			} 
			echo "<th>".$allresults['total_referral_clients']."</th>";
			echo "<th>".number_format($allresults['total_referral_clients']/$tot_mnths,2)."</th>";
			?>	
       
        <th>Refferals</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Repeats</th>
       <?php foreach($allresults['repeat_clients'] as $repeat_clients_val){
				echo "<th>".$repeat_clients_val."</th>";
			} 
			echo "<th>".$allresults['total_repeat_clients']."</th>";
			echo "<th>".number_format($allresults['total_repeat_clients']/$tot_mnths,2)."</th>";
			?>
        <th>Repeats</th>
      </tr>  
      <tr>
        <th witdh="1%" class="text-center">Total Clients</th>
        <?php foreach($allresults['total_clients'] as $total_clients_val){
				echo "<th>".$total_clients_val."</th>";
			} 
			echo "<th>".$allresults['total_total_clients']."</th>";
			echo "<th>".number_format($allresults['total_total_clients']/$tot_mnths,2)."</th>";
			?>
        <th>Total Clients</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Prebooks</th>
       <?php foreach($allresults['pre_books'] as $pre_books_val){
				echo "<th>".$pre_books_val."</th>";
			} 
			echo "<th>".$allresults['total_pre_books']."</th>";
			echo "<th>".number_format($allresults['total_pre_books']/$tot_mnths,2)."</th>";
			?>	
        <th>Prebooks</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">PB %</th>
        <?php foreach($allresults['pbPercentage'] as $pbPercentage_val){
				echo "<th>".$pbPercentage_val."%</th>";
			} 
			echo "<th>".$allresults['total_pbPercentage']."%</th>";
			echo "<th>".number_format($allresults['total_pbPercentage']/$tot_mnths,2)."%</th>";
			?>
        <th>PB %</th>
      </tr>  
      <!-- Clients data close -->
     <tr class="bg-primary">
        <td colspan="16" align="center"><b>SERVICES</b></td>
      </tr>
      <!-- Services data -->
      <tr>
        <th witdh="1%" class="text-center">Cut/Style/Wax</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>Cut/Style/Wax</th>
      </tr> 
      <tr>
        <th witdh="1%" class="text-center">Chemicals</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>Chemicals</th>
      </tr> 
      <tr>
        <th witdh="1%" class="text-center">Additional Svcs</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>Additional Svcs</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Total # Services</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>Total # Services</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Chemical % Of Total</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>0%</th>
        <th>-</th>
        <th>0%</th>
        <th>Chemical % Of Total</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">STCR (srvc to client ratio)</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>0</th>
        <th>-</th>
        <th>0</th>
        <th>STCR (srvc to client ratio)</th>
      </tr>   
      <tr class="bg-primary">
        <td colspan="16" align="center"><b>RESULTS</b></td>
      </tr>
      <!-- Results data -->
      <tr>
        <th witdh="1%" class="text-center">Service Sales</th>
       <?php foreach($allresults['service_sales'] as $service_sales_val){
				echo "<th>$".$service_sales_val."</th>";
			} 
			echo "<th>$".$allresults['total_service_sales']."</th>";
			echo "<th>$".number_format($allresults['total_service_sales']/$tot_mnths,2)."</th>";
			?>
        <th>Service Income</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Retail Sales</th>
        <?php foreach($allresults['retail_sales'] as $retail_sales_val){
				echo "<th>$".$retail_sales_val."</th>";
			} 
			echo "<th>$".$allresults['total_retail_sales']."</th>";
			echo "<th>$".number_format($allresults['total_retail_sales']/$tot_mnths,2)."</th>";
			?>
        <th>Retail Income</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Total Sales</th>
         <?php foreach($allresults['total_sales'] as $total_sales_val){
				echo "<th>$".$total_sales_val."</th>";
			} 
			echo "<th>$".$allresults['total_total_sales']."</th>";
			echo "<th>$".number_format($allresults['total_total_sales']/$tot_mnths,2)."</th>";
			?>
        <th>Total Sales</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Retail per Guest</th>
          <?php foreach($allresults['retailPerGuest'] as $retailPerGuest_val){
				echo "<th>$".$retailPerGuest_val."</th>";
			} 
			echo "<th>$".$allresults['total_retailPerGuest']."</th>";
			echo "<th>$".number_format($allresults['total_retailPerGuest']/$tot_mnths,2)."</th>";
			?>
        <th>Retail per Guest</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">RTS (Retail to Srvc)</th>
         <?php foreach($allresults['RTS'] as $RTS_val){
				echo "<th>".$RTS_val."%</th>";
			} 
			echo "<th>".$allresults['total_RTS']."%</th>";
			echo "<th>".number_format($allresults['total_RTS']/$tot_mnths,2)."%</th>";
			?>
        <th>RTS (Retail to Srvc)</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Avg Ticket Srvc Only</th>
         <?php foreach($allresults['avgTktSrvc'] as $avgTktSrvc_val){
				echo "<th>$".$avgTktSrvc_val."</th>";
			} 
			echo "<th>$".$allresults['total_avgTktSrvc']."</th>";
			echo "<th>$".number_format($allresults['total_avgTktSrvc']/$tot_mnths,2)."</th>";
			?>
        <th>Avg Ticket Srvc Only</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Average Ticket (Total)</th>
        <?php foreach($allresults['avgTktTotal'] as $avgTktTotal_val){
				echo "<th>$".$avgTktTotal_val."</th>";
			} 
			echo "<th>$".$allresults['total_avgTktTotal']."</th>";
			echo "<th>$".number_format($allresults['total_avgTktTotal']/$tot_mnths,2)."</th>";
			?>	
        <th>Average Ticket (Total)</th>
      </tr>
      <!--tr>
        <th witdh="1%" class="text-center">Total Tip Income</th>
        <th>$5,691</th>
        <th>$6,368</th>
        <th>$7,704</th>
        <th>$7,714</th>
        <th>$7,614</th>
        <th>$8,850</th>
        <th>$7,553</th>
        <th>$8,310</th>
        <th>$8,500</th>
        <th>$8,486</th>
        <th>$9,000</th>
        <th>$8,961</th>
        <th>94,751</th>
        <th>$7,896</th>
        <th>Total Tip Income</th>
      </tr>
      <tr>
        <th witdh="1%" class="text-center">Avg Monthly Income</th>
        <th>$3,702</th>
        <th>$4,524</th>
        <th>$5,057</th>
        <th>$4,838</th>
        <th>$4,857</th>
        <th>$4,595</th>
        <th>$4,060</th>
        <th>$4,494</th>
        <th>$4,317</th>
        <th>$3,728</th>
        <th>$4,116</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
      </tr-->
      <!-- Results data close -->
	   
      <!-- Services data close -->
      <?php
      if (isset($all) and $all!=''){
        foreach ($all as $key => $value) {
        	$iid = $value['iid'];
        	if(is_array($value['cfirstname'])){
        		$cfirstname = $value['cfirstname'][0];
        	}else{
        		 $cfirstname = $value['cfirstname'];
        	}
        	// SERVICE SALES
        	
        	 $millMethodParams['XmlIds'] = '<NewDataSet><Ids><Id>'.$iid.'</Id></Ids></NewDataSet>';
            $millMethodParams['StartDate'] = $start_date;
            $millMethodParams['EndDate'] = $end_date;
            $millMethodParams['IncludeVoided'] = 0;
        	
        	$servicesales = $this->nusoap_library->getMillMethodCall('GetServiceSalesByEmployee',$millMethodParams);
        	$service_total = 0;

         

          $service_total = 0;
          if(!empty($servicesales['ServiceSalesByEmployee'])){
               foreach ($servicesales['ServiceSalesByEmployee'] as $serviceKey => $serviceValue) 
               {
                 $service_total+= $serviceValue['nprice']*$serviceValue['nquantity'];
               }  
          }

        	// Product sales
        	$productsales = $this->nusoap_library->getMillMethodCall('GetProductSalesByEmployee',$millMethodParams);
        	$product_total = 0;
          if(!empty($productsales['ProductSalesByEmployee'])){
             foreach ($productsales['ProductSalesByEmployee'] as $productKey => $productValue) 
               {
                 $product_total+= $productValue['nprice']*$productValue['nquantity'];
               }  
          }
        	$total_sales = $service_total + $product_total;
        	if($total_sales==''){
        		$total_sales = 0;
        	}else{
                $total_sales = $service_total + $product_total;
        	}
        	$retail_to_service = ($service_total!=0)?($product_total/$service_total)*100:'0';
        	$retail_to_total = ($product_total!=0)?($product_total/$total_sales)*100:'0';

        	//service invoice count
        	$serviceInvoices = !empty($servicesales['ServiceSalesByEmployee']) ? 
                                array_column($servicesales['ServiceSalesByEmployee'], "cinvoiceno") : array();
            $serviceInvoicescount= sizeof(array_unique($serviceInvoices));
            //product invoice count
        	$productInvoices = !empty($productsales['ProductSalesByEmployee']) ? 
                                array_column($productsales['ProductSalesByEmployee'], "cinvoiceno") : array();
            $productInvoicescount= sizeof(array_unique($productInvoices)); 

        ?>
        <tr class="table-row" >
          <td>
            <?php echo $value['clastname'];?>,<?php echo $cfirstname?>
          </td>
          <td align="center">
            <?php echo $serviceInvoicescount;?>
          </td>
           <td align="center">
            <?php echo $productInvoicescount;?>
          </td>
          <td>$<?php echo number_format($service_total,2);?></td>
          <td>$<?php echo number_format($product_total,2);?></td>
          <td>$<?php echo number_format($total_sales,2);?></td>
          <td><?php echo number_format($retail_to_service,2);?>%</td>
          <td><?php echo number_format($retail_to_total,2);?>%</td>
         
        </tr>
        <?php
      }
    }
    else {
      ?>
      <tr><td colspan="16"><div class="alert alert-warning"><?php //print _("No Records Found.");?></div></td></tr>
      <?php
    }
    ?>
    </tbody>
  </table>
</div>

<style type="text/css">
  .action_calbtns ul li a.btn_del {
    text-align: left;
    border-radius: 0px;
    border: 0px none;
  }
</style>
<script>
function goBack() {
    window.history.back();
}
</script>
