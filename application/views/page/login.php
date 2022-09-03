<div align="center">
	<br/><br/>
	<div align="center" class="formcontainer">
		<form method="post" class="formcontent">
			<div class="formtitle">enter your information</div>
			<?php if(isset($message)) { ?><div class="formerror"><?php echo $message; ?></div><?php } ?>
		    <div class="formitem">
				<svg class="formicon"><use href="#user"/></svg><input class="formfield" type="text" name="name" placeholder="name" required autofocus>
		    </div>
		    <div class="formitem">
				<svg class="formicon"><use href="#password"/></svg><input class="formfield" type="password" name="password" placeholder="password" required>
		    </div>
		    <div class="formaction">
				<button class="formbutton" type="submit">enter</button>
		    </div>
		</form>
	</div>
</div>
<svg width="0" height="0">
	<?php
	$svgs = ["user" , "password"];
	foreach($svgs as $svg)
		{
		echo file_get_contents(site_url("assets/icons/$svg.svg"));
		}
	?>
</svg>