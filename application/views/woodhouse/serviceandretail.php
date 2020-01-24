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
<?php $this->load->view('users/headermenu');
error_reporting(E_ERROR | E_PARSE);?>
<div class="container">
  <div class="cal-main-title">
    <h1><?php echo ("Service/Retail Analysis");?> - MR041</h1>
    <h4><?php echo $displaytime;?>
    	<div class="pull-right"><button onclick="goBack()" class="btn btn-success">Back</button></div>
    </h4>
  </div>
</div>

<div class="container table-responsive" >
  <table class="table table-bordered table-hover">
    <thead>
      <tr class="bg-primary">
        <th witdh="1%" class="text-center">Name</th>
        <th witdh="5%" class="text-center">Service Tickets</th>
        <th class="">Retail Tickets</th>
        <th class="">Service Sales</th>
        <th class="">Retail Sales</th>
        <th class="">Total Sales</th>
        <th class="text-center">% Retail to Service</th>
        <th class="">% Retail to Total</th>
      </tr>
    </thead>
    <tbody>
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
      <tr><td colspan="9"><div class="alert alert-warning"><?php print _("No Records Found.");?></div></td></tr>
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
