<div>
	<div id="title" class="pagetitle"></div>
	<button class="exitbutton" type="button" onclick="togglebutton();">logout</button>
	<button class="applybutton" type="button" onclick="toggleform(<?php echo $_SESSION['userid']; ?> , 'account');">account</button>
    <input id="keyword" class="tablesearch" type="search" placeholder="search by name" onkeyup="filltable();"/><select id="filter" class="tablefilter" onchange="filltable();"></select>
    <div dir="ltr" class="tablepager">
    	<svg class="tableleft" onclick="browsetable(false);"><use href="#left"/></svg>
    	<span id="pages"></span>
    	<svg class="tableright" onclick="browsetable(true);"><use href="#right"/></svg>
    </div>
	<div id="heads" class="tablehead">
		<?php for($i = 0 ; $i < 4 ; $i ++) { ?><div class="tablename"></div><?php } ?>
	</div>
</div>
<div class="tablebody">
	<div id="contents" class="tablecontent">
		<?php for($i = 0 ; $i < 10 ; $i ++) { ?>
			<div class="tablerow">
				<div class="tablelabel">
					<span class="tablebadge"></span>
					<span class="tabletoggle" onclick="toggletable(this.nextElementSibling.id);"></span>
					<div class="tableaction">
						<svg id="editing" class="tableicon" onclick="togglename(this.parentNode.nextElementSibling , true);"><use href="#edit"/></svg>
						<svg id="deleting" class="tableicon" onclick="deleterecord(this.parentNode.id);"><use href="#delete"/></svg>
					</div>
					<form class="tableform" method="post" onsubmit="return false;">
						<input type="hidden" name="table"/>
						<input type="hidden" name="record"/>
						<input type="hidden" name="action" value="name"/>
						<input class="tablefield" type="text" name="name" placeholder="enter name" onblur="togglename(this.parentNode , false);" required/>
						<button class="tablebutton" type="submit" onclick="postform(this.parentNode);togglename(this.parentNode , false);"><svg class="tableicon"><use href="#submit"/></svg></button>
					</form>
				</div>
				<div class="tablelabel">
					<span class="tabletext"></span>
					<div class="tableaction">
						<svg id="reviewing" class="tableicon" onclick="toggleform(this.parentNode.id , 'review');"><use href="#edit"/></svg>
					</div>
				</div>
				<form class="tableform" method="post">
					<input type="hidden" name="table"/>
					<input type="hidden" name="record"/>
					<input type="hidden" name="action" value="levelid"/>
					<select class="tablebox" name="levelid" onchange="postform(this.parentNode);this.blur();" required></select>
				</form>
				<form class="tableform" method="post">
					<input type="hidden" name="table"/>
					<input type="hidden" name="record"/>
					<input type="hidden" name="action" value="assign"/>
					<input class="tablefield" type="date" name="assign" onchange="postform(this.parentNode);this.blur();" required/>
				</form>
				<form class="tableform" method="post">
					<input type="hidden" name="table"/>
					<input type="hidden" name="record"/>
					<input type="hidden" name="action" value="due"/>
					<input class="tablefield" type="date" name="due" onchange="postform(this.parentNode);this.blur();" required/>
				</form>
				<form class="tableform" method="post">
					<input type="hidden" name="table"/>
					<input type="hidden" name="record"/>
					<input type="hidden" name="action" value="stateid"/>
					<select class="tablebox" name="stateid" onchange="postform(this.parentNode);this.blur();" required></select>
				</form>
			</div>
		<?php } ?>
	</div>
	<div class="tablenone">No results!</div>
</div>
<form id="inserts" class="tableinsert" method="post" onsubmit="return postform(this);">
	<input class="tablebutton" type="reset" value="Insert" onclick="toggleinsert(this);"/>
	<input type="hidden" name="table"/>
	<input type="hidden" name="userid"/>
	<input type="hidden" name="stateid" value="1"/>
	<input type="hidden" name="action" value="insert"/>
	<input class="tablefield" type="text" name="name" placeholder="enter name"/>
	<input class="tablefield" type="email" name="email" placeholder="enter email"/>
	<input class="tablefield" type="password" name="password" placeholder="enter password"/>
	<input class="tablefield" type="date" name="assign"/>
	<input class="tablefield" type="date" name="due"/>
	<select class="tablebox" name="levelid"></select>
	<input class="tablebutton" type="submit" value="Insert"/>
</form>