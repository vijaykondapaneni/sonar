<style type="text/css">
* {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

body {
  padding: 20px 15%;
}
form header {
  margin: 0 0 20px 0; 
}
form header div {
  font-size: 90%;
  color: #999;
}
form header h2 {
  margin: 0 0 5px 0;
}
form > div {
  clear: both;
  overflow: hidden;
  padding: 1px;
  margin: 0 0 10px 0;
}
form > div > fieldset > div > div {
  margin: 0 0 5px 0;
}
form > div > label,
legend {
  width: 25%;
  float: left;
  padding-right: 10px;
}
form > div > div,
form > div > fieldset > div {
  width: 75%;
  float: right;
}
form > div > fieldset label {
  font-size: 90%;
}
fieldset {
  border: 0;
  padding: 0;
}

input[type=text],
input[type=email],
input[type=url],
input[type=password],
textarea {
  width: 100%;
  border-top: 1px solid #ccc;
  border-left: 1px solid #ccc;
  border-right: 1px solid #eee;
  border-bottom: 1px solid #eee;
}
input[type=text],
input[type=email],
input[type=url],
input[type=password] {
  width: 50%;
}
input[type=text]:focus,
input[type=email]:focus,
input[type=url]:focus,
input[type=password]:focus,
textarea:focus {
  outline: 0;
  border-color: #4697e4;
}

@media (max-width: 600px) {
  form > div {
    margin: 0 0 15px 0; 
  }
  form > div > label,
  legend {
    width: 100%;
    float: none;
    margin: 0 0 5px 0;
  }
  form > div > div,
  form > div > fieldset > div {
    width: 100%;
    float: none;
  }
  input[type=text],
  input[type=email],
  input[type=url],
  input[type=password],
  textarea,
  select {
    width: 100%; 
  }
}
@media (min-width: 1200px) {
  form > div > label,
  legend {
    text-align: right;
  }
}
</style>
  <header>
    <h2>New Salon Details</h2>
  </header>
  <form method="post" action="<?php echo base_url();?>index.php/NewSalon/addsalon">
  <div>
    <label class="desc" id="title1" for="Field1">Salon Name <font color="red">*</font></label>
    <div>
      <input id="salon_name" name="salon_name" required="" type="text" class="field text fn" value="" size="8" tabindex="1">
    </div>
  </div>
  <div>
    <fieldset>
      <legend id="title5" class="desc">
        Salon Type <font color="red">*</font>
      </legend>
      <div>
        <div>
          <input id="Field5_0" name="salon_type" type="radio" value="1" tabindex="5" checked="checked">
          <label class="choice" for="Field5_0">Commision</label>
        </div>
        <div>
          <input id="Field5_1" name="salon_type" type="radio" value="2" tabindex="6">
          <label class="choice" for="Field5_1">Team</label>
        </div>
        <div>
      </div>
    </fieldset>
  </div>
  <div>
    <label class="desc" id="title1" for="Field1">Salon Id <font color="red">*</font></label>
    <div>
      <input id="salon_id" required="" name="salon_id" type="text" class="field text fn" value="" size="8" tabindex="1">
    </div>
  </div>
  <div>
    <label class="desc" id="title1" for="Field1">Salon Account / Code Id <font color="red">*</font></label>
    <div>
      <input id="salon_account_id" required="" name="salon_account_id" type="text" class="field text fn" value="" size="8" tabindex="1">
    </div>
  </div>
  <div>
    <label class="desc" id="title1" for="Field1">Mill Username <font color="red">*</font></label>
    <div>
      <input id="salon_account_id" required="" name="mill_username" type="text" class="field text fn" value="" size="8" tabindex="1">
    </div>
  </div>
  <div>
    <label class="desc" id="title1" for="Field1">Mill Password <font color="red">*</font> </label>
    <div>
      <input id="mill_password" required="" name="mill_password" type="text" class="field text fn" value="" size="8" tabindex="1">
    </div>
  </div>
  <div>
    <label class="desc" id="title1" for="Field1">Mill GUID <font color="red">*</font></label>
    <div>
      <input id="mill_guid" required="" name="mill_guid" type="text" class="field text fn" value="" size="8" tabindex="1">
    </div>
  </div>
  <div>
    <label class="desc" id="title1" for="Field1">Mill URL <font color="red">*</font></label>
    <div>
      <input id="mill_url" required="" name="mill_url" type="text" class="field text fn" value="" size="8" tabindex="1">
    </div>
  </div>
  <div>
		<div>
     	<input id="saveForm" name="saveForm" type="submit" value="Submit">
    </div>
	</div>
  
</form>