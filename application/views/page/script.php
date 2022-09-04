<script type="text/javascript">
	let tablepage = 1;
	let eventscount = 0;
	let tableboxes = {};
	let tablefields = [];
	let tableresult = [];
	let tabledisables = [];
	let tablereference = [];
	<?php if($_SESSION['levelid'] == 1) { ?>
	let tablename = "tasks";
	let tablerecord = "<?php echo $_SESSION['userid']; ?>";
	<?php } else { ?>
	let tablename = "users";
	let tablerecord = "";
	<?php } ?>
	const backend = "<?php echo site_url('api'); ?>";
	const events = new EventSource(backend + "/notify");
	const tabletitle = document.getElementById("title");
	const searchfield = document.getElementById("keyword");
	const filterbox = document.getElementById("filter");
	const pagecount = document.getElementById("pages");
	const tableheads = document.getElementById("heads");
	const tablecontents = document.getElementById("contents");
	const insertelements = document.getElementById("inserts");
	const formoverlay = document.getElementById("overlay");
	const formelements = formoverlay.firstElementChild;
	const formtitle = formoverlay.firstElementChild.firstElementChild;
	const formblocks = document.getElementById("blocks");
	const loadoverlay = document.getElementById("loading");
	const eventsound = new Audio("<?php echo site_url('assets/bell.mp3'); ?>");
	const tablemapping = ["name" , "reviewers" , "levelid" , "assign" , "due" , "stateid"];
	function togglebutton()
		{
		<?php if($_SESSION['levelid'] == 1) { ?>
		window.location = "<?php echo site_url('logout'); ?>";
		<?php } else { ?>
		if(tablename == "users")
			{
			window.location = "<?php echo site_url('logout'); ?>";
			}
		else if(tablename == "tasks")
			{
			tablerecord = "";
			tablename = "users";
			tabletitle.nextElementSibling.textContent = "logout";
			loadtable();
			}
		<?php } ?>
		}
	function toggleview(element , flag)
		{
		if(flag)
			{
			element.style.display = "flex";
			setTimeout(() => { element.style.opacity = 1; } , 20);
			}
		else
			{
			element.style.opacity = 0;
			setTimeout(() => { element.style.display = "none"; } , 200);
			}
		}
	function fetcherror(message)
		{
		alert(message);
		if(confirm("reload portal?"))
			{
			window.location.reload();
			}
		}
	function loadtable(refresh = false)
		{
		toggleview(loadoverlay , true);
		fetch(backend + "/table/" + tablename + "/" + tablerecord).then(request => request.json()).then(response =>
			{
			if(response.status == 0)
				{
				tablefields = response.body.fields;
				tablereference = response.body.values;
				tabledisables = response.body.disables;
				if(!refresh)
					{
					searchfield.value = "";
					tabletitle.textContent = response.body.title;
					tableboxes = response.body.boxes
					buildfilter(response.body.filters);
					buildtable(response.body.heads , response.body.actions);
					buildform(response.body.forms);
					}
				filltable();
				}
			else
				{
				fetcherror(response.body);
				}
			});
		}
	function buildfilter(filters)
		{
		if(filters.length == 0)
			{
			filterbox.style.display = "none";
			}
		else
			{
			filterbox.removeAttribute("style");
			filterbox.innerHTML = filters.map((filter) => { return "<option value='" + filter.id + "'>" + filter.name + "</option>"; } ).join("");
			}
		}
	function buildtable(heads , actions)
		{
		Array.prototype.forEach.call(tableheads.children , (child , step) =>
			{
			if(step < heads.length)
				{
				child.removeAttribute("style");
				child.textContent = heads[step];
				}
			else
				{
				child.style.display = "none";
				}
			});
		Array.prototype.forEach.call(tablecontents.children , (row) =>
			{
			Array.prototype.forEach.call(row.children , (cell , step) =>
				{
				if(tablefields.includes(tablemapping[step]))
					{
					cell.removeAttribute("style");
					if(tablemapping[step] == "name")
						{
						Array.prototype.forEach.call(cell.children[2].children , (action) =>
							{
							if(actions.includes(action.id))
								{
								action.removeAttribute("style");
								}
							else
								{
								action.style.display = "none";
								}
							});
						}
					}
				else
					{
					cell.style.display = "none";
					}
				});
			});
		}
	function filltable()
		{
		tableresult = tablereference;
		if(searchfield.value !== "")
			{
			tableresult = tableresult.filter((item) => { return item.name.toLowerCase().includes(searchfield.value.toLowerCase()); });
			}
		if(filterbox.style.display !== "none" && filterbox.value !== "0")
			{
			tableresult = tableresult.filter((item) =>
				{
				if(tablename == "users")
					{
					return item.levelid == filterbox.value;				
					}
				else if(tablename == "tasks")
					{
					return item.stateid == filterbox.value;
					}
				});
			}
		toggleview(loadoverlay , false);
		viewtable();
		}
	function viewtable()
		{
		if(tableresult.length == 0)
			{
			pagecount.textContent = "0 / 0";
			tablecontents.style.display = "none";
			tablecontents.nextElementSibling.style.display = "block";
			}
		else
			{
			if(Math.ceil(tableresult.length / 10) < tablepage)
				{
				tablepage --;
				}
			tablecontents.removeAttribute("style");
			tablecontents.nextElementSibling.removeAttribute("style");
			pagecount.textContent = tablepage + " / " + Math.ceil(tableresult.length / 10);
			for(let counter = tablepage ; counter <= tablepage * 10 ; counter ++)
				{
				let row = tablecontents.children[counter - 1];
				if(tableresult.length > (counter - 1))
					{
					row.style.display = "grid";
					tablefields.forEach((field) =>
						{
						let step = 0;
						let cell = row.children[tablemapping.indexOf(field)];
						if(cell.getAttribute("class") == "tablelabel")
							{
							cell.removeAttribute("style");
							if(cell.children[0].getAttribute("class") == "tablebadge")
								{
								step ++;
								cell.children[0].textContent = tableresult[counter - 1]['events'];
								if(tableresult[counter - 1]['events'] == 0)
									{
									cell.children[0].removeAttribute("style");
									}
								else
									{
									cell.children[0].style.color = "white";
									cell.children[0].style.background = "red";
									}
								}
							cell.children[step].textContent = tableresult[counter - 1][field];
							cell.children[step + 1].id = tableresult[counter - 1].id;
							if(cell.children.length > step + 2)
								{
								cell.children[step + 2].table.value = tablename;
								cell.children[step + 2].record.value = tableresult[counter - 1].id;
								cell.children[step + 2].name.value = tableresult[counter - 1][field];
								}
							}
						else if(cell.getAttribute("class") == "tableform")
							{
							cell.removeAttribute("style");
							if(cell[field].getAttribute("class") == "tablebox")
								{
								cell[field].innerHTML = tableboxes[field].map((option) =>
									{
									if(option.id == "")
										{
										return "<option value='" + option.id + "' selected disabled>" + option.name + "</option>";
										}
									else
										{
										return "<option value='" + option.id + "'>" + option.name + "</option>";
										}
									}).join("");
								}
							cell.table.value = tablename;
							cell.record.value = tableresult[counter - 1].id;
							cell[field].value = tableresult[counter - 1][field];
							if(tabledisables.includes(field))
								{
								cell[field].disabled = true;
								}
							else
								{
								cell[field].disabled = false;
								}
							if(field == "stateid")
								{
								colortable(cell , tableresult[counter - 1].stateid);
								}
							}
						else
							{
							cell.style.display = "none";
							}
						});
					}
				else
					{
					row.removeAttribute("style");
					}
				}
			}
		}
	function browsetable(flag)
		{
		const total = Math.ceil(tableresult.length / 10);
		if(flag && tablepage + 1 < total)
			{
			tablepage ++;
			}
		else if(!flag && tablepage > 1)
			{
			tablepage --;
			}
		viewtable();
		}
	function colortable(cell , stateid)
		{
		switch(stateid)
			{
			case "1":
				cell.style.backgroundColor = "lightyellow";
				break;
			case "2":
				cell.style.backgroundColor = "lightblue";
				break;
			case "3":
				cell.style.backgroundColor = "lightgreen";
				break;
			default:
				cell.style.backgroundColor = "#eee";
			}
		}
	function toggletable(record = "")
		{
		switch(tablename)
			{
			case "users":
				tablename = "tasks";
				tablerecord = record;
				tabletitle.nextElementSibling.textContent = "previous";
				loadtable();
				break;
			case "tasks":
				toggleform(record , "comment");
				break;
			}
		}
	function togglename(form , flag)
		{
		if(flag)
			{
			form.name.value = form.previousElementSibling.previousElementSibling.textContent;
			form.style.display = "flex";
			form.name.focus();
			}
		else
			{
			setTimeout(() => { form.removeAttribute("style"); } , 100);
			}
		}
	function buildform(fields , title = "" , parameters = {})
		{
		let elements = null;
		if(title.length == 0)
			{
			insertelements.reset();
			insertelements.table.value = tablename;
			if(tablename == "tasks")
				{
				insertelements.record.value = tablerecord;
				}
			else
				{
				insertelements.record.value = 1;
				}
			elements = insertelements;
			}
		else
			{
			formtitle.textContent = title;
			formelements.table.value = parameters.table;
			formelements.action.value = parameters.action;
			formelements.record.value = parameters.record;
			elements = formelements;			
			}
		Array.prototype.forEach.call(elements , (field) =>
			{
			if(fields.includes(field.name))
				{
				if(field.name != "password")
					{
					field.required = true;
					}
				if(title.length == 0)
					{
					field.removeAttribute("style");
					}
				else
					{
					field.parentNode.removeAttribute("style");
					}
				if(field.getAttribute("class").includes("tablebox"))
					{
					fetch(backend + "/select/" + field.name).then(request => request.json()).then(response =>
						{
						if(response.status == 0)
							{
							field.innerHTML = response.body.select.map((option) =>
								{
								if(option.id == "")
									{
									return "<option value='" + option.id + "' selected disabled>" + option.name + "</option>";
									}
								else
									{
									return "<option value='" + option.id + "'>" + option.name + "</option>";
									}
								}).join("");
							}
						else
							{
							fetcherror(response.body);
							}
						});
					}
				}
			else if(field.type != "hidden" && field.type != "button" && field.type != "reset")
				{
				if(field.type == "submit")
					{
					if(fields.length == 0)
						{
						field.style.display = "none";
						}
					else
						{
						field.removeAttribute("style");
						}
					}
				else
					{
					field.required = false;
					if(title.length == 0)
						{
						field.style.display = "none";
						}
					else
						{
						field.parentNode.style.display = "none";
						}
					}
				}
			});
		}
	function postform(form , popup = false)
		{
		toggleview(loadoverlay , true);
		fetch(backend + "/form/" + form.table.value + "/" + form.action.value + "/" + form.record.value , { method: "post" , body: new FormData(form) }).then(request => request.json()).then(response =>
			{
			if(response.status == 0 && response.body == "success")
				{
				toggleview(loadoverlay , false);
				if(tablename == form.table.value || form.table.value == "reviewers")
					{
					loadtable(true);
					}
				if(form.action.value == "insert")
					{
					form.reset();
					if(insertelements.hasAttribute("style"))
						{
						toggleinsert(insertelements.firstElementChild);
						}
					}
				}
			else
				{
				fetcherror(response.body);
				}
			if(popup)
				{
				closeform();
				}
			});
		return false;
		}
	function toggleform(record , action)
		{
		let table = "";
		let title = "";
		let fields = [];
		switch(action)
			{
			case "account":
				title = "Account";
				table = "users";
				fields = ["name" , "email" , "password"];
				break;
			case "review":
				title = "Reviewers";
				table = "reviewers";
				fields = ["reviewer"];
				break;
			case "comment":
				title = "Comments";
				table = "comments";
				fields = ["comment"];
				break;
			}
		buildform(fields , title , { action: action , table: table , record: record });
		fillform({ action: action , table: table , record: record });
		toggleview(formoverlay , true);
		}
	function fillform(parameters)
		{
		toggleview(loadoverlay , true);
		fetch(backend + "/form/" + parameters.table + "/" + parameters.action + "/" + parameters.record).then(request => request.json()).then(response =>
			{
			if(response.status == 0)
				{
				if(parameters.table == "reviewers")
					{
					formblocks.removeAttribute("style");
					if(response.body.values.length == 0)
						{
						formblocks.removeAttribute("class");
						formelements.save.style.display = "none";
						formblocks.innerHTML = "<div class='tablenone' style='display:block;'>No submanagers!</div>";
						}
					else
						{
						formblocks.setAttribute("class" , "formchecks");
						formblocks.innerHTML = response.body.values.map((check) =>
							{
							if(check.selected > 0)
								{
								return "<label for='reviewer" + check.id + "'><input id='reviewer" + check.id + "' class='formcheck' type='checkbox' name='reviewerid[]' value='" + check.id + "' checked/>" + check.name + "</label>";
								}
							else
								{
								return "<label for='reviewer" + check.id + "'><input id='reviewer" + check.id + "' class='formcheck' type='checkbox' name='reviewerid[]' value='" + check.id + "'/>" + check.name + "</label>";
								}
							}).join("");
						}
					}
				else if(parameters.table == "comments")
					{
					formblocks.removeAttribute("style");
					formblocks.removeAttribute("class");
					if(response.body.values.length == 0)
						{
						formblocks.innerHTML = "<div class='tablenone' style='display:block;'>No comments!</div>";						
						}
					else
						{
						formblocks.innerHTML = response.body.values.map((comment) => { return "<div class='formcomment'><b>" + comment.name + "</b><br/>" + comment.time + "<br/>" + comment.comment + "</div>" }).join("");
						}
					}
				else
					{
					for(let key in response.body.values)
						{
						formelements[key].value = response.body.values[key];
						}
					formblocks.style.display = "none";
					}
				}
			else
				{
				fetcherror(response.body);
				}
			toggleview(loadoverlay , false);
			});
		}
	function closeform()
		{
		toggleview(formoverlay , false);
		setTimeout(() => { formoverlay.firstElementChild.reset(); } , 200);
		}
	function deleterecord(record)
		{
		if(confirm("Are you sure you want to delete this item (and all its relevant data)?"))
			{
			toggleview(loadoverlay , true);
			fetch(backend + "/delete/" + tablename + "/" + record).then(request => request.json()).then(response =>
				{
				if(response.status == 0 && response.body == "success")
					{
					loadtable(true);
					}
				else
					{
					fetcherror(response.body);
					}
				toggleview(loadoverlay , false);
				});
			}
		}
	function toggleinsert(element)
		{
		if(insertelements.hasAttribute("style"))
			{
			element.value = "Insert";
			element.parentNode.removeAttribute("style");
			}
		else
			{
			element.value = "Close";
			element.parentNode.style.height = "auto";
			}
		}
	events.onmessage = function(event)
		{
		const count = JSON.parse(event.data);
		if(count > eventscount)
			{
			eventsound.play().then(ـ => {}).catch(error => {});
			}
		if(count != eventscount)
			{
			loadtable(true);			
			}
		eventscount = count;
		};
	loadtable();
</script>