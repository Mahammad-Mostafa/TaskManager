<div id="overlay" class="formoverlay">
	<form class="formcontainer" method="post" onsubmit="return postform(this , true);">
		<div class="formtitle"></div>
		<div class="formbody">
			<div class="formcontent">
				<div id="blocks" class="formchecks"></div>
				<div class="formitem">
					<svg class="formicon"><use href="#name"/></svg><input class="formfield" type="text" name="name" placeholder="name goes here"/>
			    </div>
			    <div class="formitem">
					<svg class="formicon"><use href="#email"/></svg><input class="formfield" type="email" name="email" placeholder="email goes here"/>
			    </div>
			    <div class="formitem">
					<svg class="formicon"><use href="#password"/></svg><input class="formfield" type="password" name="password" placeholder="password goes here"/>
			    </div>
				<div class="formitem formarea">
					<svg class="formicon"><use href="#text"/></svg><textarea class="formfield formarea" name="description" placeholder="description goes here"></textarea>
			    </div>
			    <div class="formitem formarea">
					<svg class="formicon"><use href="#comment"/></svg><textarea class="formfield formarea" name="comment" placeholder="comment goes here"></textarea>
			    </div>
			</div>
		</div>
		<input type="hidden" name="action"/><input type="hidden" name="table"/><input type="hidden" name="record"/>
		<div class="formaction">
		    <button class="formbutton" type="button" onclick="closeform();">back</button>
			<button class="formbutton" type="submit" name="save">save</button>
		</div>
	</form>
</div>
<div id="loading" class="loaderoverlay"><svg class="loadericon"><use href="#loader"/></svg></div>
<svg width="0" height="0">
	<?php
	$svgs = get_filenames("assets/icons");
	foreach($svgs as $svg)
		{
		echo file_get_contents(site_url("assets/icons/$svg"));
		}
	?>
</svg>