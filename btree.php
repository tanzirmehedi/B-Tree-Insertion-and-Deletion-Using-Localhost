<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="author" content="Graham O'Neill, http://goneill.co.nz">
<title>Shawon-PC B+ Tree demo</title>
<script src="btree.js"></script>
<script src="btree-show.js"></script>

<style type="text/css">


HTML {	overflow-y: scroll;}

BODY {
	background-color: #fff;
	text-align: left;
	font-family: verdana, arial, sans-serif;
	font-size: 10pt;
}

*    {
	margin: 0;
	padding: 0;
	color: #3CC;
}

#content {
	background-color: #FFF;
	padding: 10px 8px 15px 8px;
	position: relative;}

h2 {	font-family: verdana, arial, sans-serif;
	font-size: 10pt;
	font-weight: bold;}

.formbox {
	padding: 15px 0px 15px 15px;
	border: 3px solid #3CC;
	width:30%;
	margin-left:450px;
	
	}

.formline {
	padding: 5px 0px 5px 0px;
	background-color: #FFF;
}

form label {
	float:left;
	padding: 4px 0px 0px 0px;
	width: 90px;}

form input,textarea,select {
	padding: 3px 4px 3px 4px; 
	border: 1px solid #3CC;
	border-radius: 5px;
	font-family: arial, sans-serif;
	font-size: 10pt;}

form input:focus,textarea:focus,select:focus { 
	box-shadow: 0 0 5px #3CC;}

.btn {
	color:#FFF;
	background:#3CC;
	border-radius: 4px 4px 4px 4px;
	box-shadow: 1px 1px 2px;
	font-family: arial, sans-serif;
	font-size: 10pt;
	width:80px;
	font-weight: bold;
	cursor: pointer;
	padding-top: 2px;
	padding-right: 3px;
	padding-bottom: 2px;
	padding-left: 3px;
}
.btn:hover {
	color:#FFF;
	background:#999;}
.btn:active {
	color:#999;
	background:#FFF;}

TABLE {	border: 10px solid #c0c0c0;
	border-spacing: 0px;
	border-collapse: collapse;
	font-family: arial, sans-serif;
	font-size: 10pt;}

#actForm {
	margin: 5px 10px 10px 10px;}

.showBox {
	margin: 5px 0px 15px 0px;
	padding: 10px;
	color: #FFF;
	background-color: #FFF;
	border: 3px solid #3CC;
	font-family: Arial;
	font-size: 10pt;
	overflow-x: auto;
	width: 50%;
	margin-left: 320px;
}

CANVAS {display: block;}

.trLvl {padding: 2px;}

TABLE.trNode TD {
	border: 10px solid #000;
	background-color: #3CC;
	padding: 2px 2px 2px 2px;}

TABLE.trLeaf TD {
	border: 1px solid #000;
	background-color: #3FF;
	padding: 2px 2px 2px 2px;
}

TABLE.trLeaf TD.here {
	border: 1px solid #000;
	background-color: #CF0;}

</style>

<script>

var myTree = null;
var hist = [];
var pool = [];
var maxDisplay;
var isBusy = false;

modAct = function(act) {
	var txt, dis=false, num='';
	switch (act) {
		case 'bld':
			txt = 'Pointers';
			break;
		case 'add':
		case 'del':
		case 'seek':
		case 'near':
			txt = 'Key Value';
			break;
		case 'goto':
			txt = 'Key Number';
			break;
		case 'top':
		case 'bot':
		case 'pack':
		case 'hist':
		case 'run':
		case 'hide':
		case 'show':
			txt = '';
			dis=true;
			break;
		case 'rand':
			num = pool.length;
		case 'skip':
		case 'init':
		case 'time':
			txt = 'No of keys';
			break;
	}
	ge$('labl').innerHTML = txt;
	ge$('num').value = num;
	ge$('num').disabled = dis;
	if (dis) ge$('btn').focus();
	else     endCursor(ge$('num'));
}

runAct = function(act,num) {
	if (isBusy) return;

	num = parseInt(num,10);
	if (isNaN(num)) num = 0;

	if (act == 'hide') {
		ge$('frDiv').style.display = 'none';
		ge$('act').focus();
		return;
	}
	if (act == 'show') {
		ge$('frDiv').style.display = '';
		ge$('act').focus();
		return;
	}

	var txt = '';
	if (myTree!==null) txt = myTree.show('frCanvas');
	ge$('frMsg').innerHTML = txt;

	txt = '';
	if ('bld~init~hide~show'.search(act)==-1 && myTree===null) {
		txt = 'Error: you have to build the tree first.';
	} else
	if (act=='bld' && num<3) {
		txt = 'Error: the tree must have Order of at least 3.';
	} else
	if ('add~seek~init~rand~time'.search(act)!=-1 && num<=0) {
		txt = 'Error: invalid number given. Must be greater than zero.';
	}
	if (txt.length>0) act = 'error';

	var foc = 'act';
	switch (act) {
		case 'error':
			break;
		case 'bld':
			myTree = new tree(num);
			maxDisplay = num * 50;
			hist = [];
			hist[0] = 'myTree = new tree('+num+');'; 
			break;
		case 'add':
			myTree.insert(num,num);
			hist.push('myTree.insert('+num+','+num+');');
			foc = 'num';
			ge$('num').value = '';
			break;
		case 'del':
			if (num == 0) {
				myTree.remove();
				hist.push('myTree.remove();');
			} else {
				myTree.remove(num);
				hist.push('myTree.remove('+num+');');
			}
			foc = 'num';
			ge$('num').value = '';
			break;
		case 'seek':
			myTree.seek(num);
			hist.push('myTree.seek('+num+');');
			foc = 'num';
			break;
		case 'near':
			myTree.seek(num,true);
			hist.push('myTree.seek('+num+',true);');
			foc = 'num';
			break;
		case 'goto':
			myTree.goto(num);
			hist.push('myTree.goto('+num+');');
			break;
		case 'top':
			myTree.goTop();
			hist.push('myTree.goTop();');
			break;
		case 'bot':
			myTree.goBottom();
			hist.push('myTree.goBottom();');
			break;
		case 'pack':
			myTree.pack();
			hist.push('myTree.pack();');
			break;
		case 'skip':
			if (num == 0 || num == 1) {
				myTree.skip();
				hist.push('myTree.skip();');
			} else {
				myTree.skip(num);
				hist.push('myTree.skip('+num+');');
			}
			foc = 'btn';
			break;
		case 'init':
			pool = [];
			for (i=0; i<num; i++) {
				pool[i] = i+1;
			}
			pool.shuffle();
			txt = 'Pool set up with '+commas(num)+' keys.<br>\r\n';
			break;
		case 'rand':
			var i=0;
			while (i<num && pool.length>0) {
				myTree.insert(pool.pop(),'');
				if (!myTree.found) i++;
			}
			if (i<num) txt = 'Error: ran out of unique keys in the pool. Only '+commas(i)+' added.';
			hist.push('// '+i+' random keys added to tree');
			break;
		case 'time':
			var ord = myTree.maxkey + 1;
			myTree = null;
			pool = [];
			for (i=0; i<num; i++) {
				pool[i] = i+1;
			}
			pool.shuffle();
			if (num <= 2000000) {
				var start = new Date().getTime();
				myTree = new tree(ord);
				for (var i=num; i>0; i--) {
					myTree.insert(pool.pop(),'');
				}
				var end = new Date().getTime();
				txt = 'Tree rebuilt and '+commas(myTree.length)+' random keys added in '+commas(end-start)+'ms<br><br>\r\n';
				start = new Date().getTime();
				for (var i=num; i>0; i--) {
					myTree.seek(i,false);
					// if (myTree.keyval != i) {
					//	alert('Key '+i+' missing!');
					//	break;
					// }
				}
				end = new Date().getTime();
				txt += 'Seek for every key completed in '+commas(end-start)+'ms\r\n'; 
			} else {
				isBusy = true;
				var start = new Date().getTime();
				myTree = new tree(ord);
				addLots(start, num, num);
				txt = 'Working... Please wait'; 
			}
			hist = [];
			hist[0] = 'myTree = new tree('+ord+');'; 
			hist.push('// '+num+' random keys added to tree');
			break;
		case 'run':
			Hardcoded();
			hist.push('// Hardcoded script run');
			break;
		case 'hist':
			for (var i=0, len=hist.length; i<len; i++) {
				txt += hist[i] + '<br>\r\n';
			}
	}

	if (myTree!==null) {
		if (txt.length==0) txt = myTree.show('toCanvas');
		else               myTree.showoff('toCanvas');
	}
	ge$('toMsg').innerHTML = txt;
	if (foc!='num') ge$(foc).focus();
	else            endCursor(ge$('num'));
}

Hardcoded = function () {
myTree.insert(1,'');
myTree.insert(15,'');
myTree.insert(4,'');
myTree.insert(10,'');
myTree.insert(16,'');
myTree.insert(11,'');
myTree.insert(13,'');
myTree.insert(12,'');
myTree.insert(20,'');
myTree.insert(9,'');
myTree.insert(25,'');
}

function addLots(strTim, totnum, remain) {
	var doNow = Math.min(remain, 1000000);
	for (var i=doNow; i>0; i--) {
		myTree.insert(pool.pop(),'');
	}
	remain = remain - doNow;
	if (remain > 0) {
		setTimeout(function(){addLots(strTim,totnum,remain);}, 0);
	} else {
		var endTim = new Date().getTime();
		txt = 'Tree rebuilt and '+commas(myTree.length)+' random keys added in '+commas(endTim-strTim)+'ms<br><br>\r\n'; 
		strTim = new Date().getTime();
		for (var i=totnum; i>0; i--) {
			myTree.seek(i,false);
		}
		endTim = new Date().getTime();
		txt += 'Seek for every key completed in '+commas(endTim-strTim)+'ms\r\n'; 
		ge$('toMsg').innerHTML = txt;
		isBusy = false;
	}
}

Array.prototype.shuffle = function () {
	for (var i=this.length-1; i>0; i--) {
		var j = Math.floor(Math.random() * (i+1));
		var tmp = this[i];
		this[i] = this[j];
		this[j] = tmp;
	}
	return this;
}

/* General JS */

commas = function (x) {return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");}

enterToTab = function (obj,e) {
	var e = (typeof event!='undefined') ? window.event : e;	// IE : Mozilla 
	if (e.keyCode==13) {
		var q;
		var ele = document.forms[0].elements;
		for (var i=0, len=ele.length; i<len; i++) {
			q = (i==ele.length-1) ? 0: i+1;
			if (obj==ele[i]) {
				ele[q].focus();
				break;
			}
		}
		return false;
	}
}

function ge$(d) {
	var x = document.getElementById(d);
	if (!x) {
		var y = document.getElementsByName(d);
		if (y.length>0) x = y[0];
	}
	return x;
}

function endCursor(el) {
	el.focus();
	if (el.setSelectionRange) {
		var endPos = el.value.length;
		el.setSelectionRange(endPos, endPos);
	}
}

function debug(txt) {window.console && console.log(txt);}

</script>

<br>
<br>
<div>
<h3 align="center" id="col"> SK. Tanzir Mehedi Shawon </h3>
<h2 align="center"> Department of ICT</h2>
<h2 align="center"> Mawlana Bhashani Science and Technology University</h2>
</div>

<br>
<br>
</head>


<body onload="document.actForm.num.focus()">

<div id="content">

<h2 align="center">Action To Generate B+ Tree</h2>
<form  name="actForm" id="actForm">
<div class="formbox">
<div class="formline">
<label for="act">Selection  </label>
<select name="act" id="act" width="150 " style="width:150px" onChange="modAct(this.value)">
  <option value="bld">Build B+ Tree</option>
  <option value="add">Insert</option>
  <option value="del">Delete</option>
</select>
</div>
<div class="formline"><label id="labl" for="num">Pointers</label><input name="num" id="num" size="5" maxlength="8" onkeypress="return enterToTab(this,event)" type="text"></div>
<div class="formline"><label>&nbsp;</label><input id="btn" class="btn" value="Genetate" onclick="runAct(document.actForm.act.value, document.actForm.num.value)" type="button"></div>
</div>
</form>

<div id="frDiv">
<h2 align="center">Previous Step</h2>
<div class="showBox">
<div id="frMsg"></div>
<canvas id="frCanvas" width="1" height="1"></canvas>
</div>
</div>

<h2 align="center">Present Result</h2>
<div class="showBox">
<div id="toMsg"></div>
<canvas id="toCanvas" width="1" height="1"></canvas>
</div>

</div>
<h2 align="center">
<input id="btn" class="btn" value="Reset"  align="middle" <a href="#" onclick="location.href='btree.php';return false" type="button"></h2>
<br>
<br>
<br>
<br>

<h2 align="center">Developed By: SK. Tanzir Mehedi Shawon</h2>
</body>
​​​​​</html>
