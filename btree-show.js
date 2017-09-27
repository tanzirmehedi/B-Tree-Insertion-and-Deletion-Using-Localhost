
var treeHTML;
var useCanvas = !!document.createElement('canvas').getContext;

tree.prototype.show = function (canvasId) {
	treeHTML = '\r\nfound: '+this.found.toString().toUpperCase()+'&nbsp;&nbsp;&nbsp;&nbsp;eof: '+this.eof.toString().toUpperCase()+'\r\n';
	if (this.length > maxDisplay) {
		treeHTML += '<br><br>\r\n'+'Tree is very big ('+commas(this.length)+' keys).';
		if (!this.eof) treeHTML += ' Current key: '+this.leaf.keyval[this.item];
		treeHTML += '<br>\r\n';
		this.showoff(canvasId);
		return treeHTML;
	}
	if (useCanvas) {
		this.drawInit(canvasId);
		this.drawNode(this.root, 0);
	} else {
		this.listNode(this.root, 0);
	}
	return treeHTML;
};

tree.prototype.showoff = function (canvasId) {
	if (useCanvas) {
		var canv = ge$(canvasId);
		canv.width = 1;
		canv.height = 1;
	}
};

// ---- List mode for older browsers ----

tree.prototype.listNode = function (ptr,lvl) {
	treeHTML += '<div class="trLvl" style="padding-left:'+ (lvl*40) + 'px;"><table class="';
	if (ptr.isLeaf()) treeHTML += 'trLeaf';
	else              treeHTML += 'trNode';
	treeHTML += '"><tbody><tr>';
	for (var i=0, len=this.maxkey; i<len; i++) {
		if (ptr==this.leaf && i==this.item) treeHTML += '<td class="here">';
		else                                treeHTML += '<td>';
		if (i < ptr.keyval.length) treeHTML += ptr.keyval[i]+'</td>';
		else                       treeHTML += '&nbsp;</td>';
	}
	treeHTML += '</tr></tbody></table></div>\r\n';
	if (ptr.isLeaf()) return;
	for (var i=0, len=ptr.keyval.length; i<=len; i++) {
		this.listNode(ptr.nodptr[i],lvl+1);
	}
};


// ---- Canvas mode ----

tree.prototype.drawInit = function (cId) {
	// Canvas
	this.canvas = ge$(cId);
	this.contex = this.canvas.getContext('2d');

	// Colours
	this.Nfill = '#D2B48C';
	this.Nline = '#8C6414';
	this.Pfill = '#880015';
	this.Pline = '#880015';
	this.Lfill = '#90EE90';
	this.Lline = '#008000';
	this.Cfill = '#FFAAAA';
	this.Cline = '#CC0000';
	this.Tline = '#000000';

	// Position and sizes
	this.Tfont = '15px arial';
	this.Tsize = 15;
	this.curLeft = 0;
	this.vPad = this.maxkey * 10;
	this.hPad = 15;

	var d=0, w=0;
	var ptr = this.root;
	while (!ptr.isLeaf()) {
		ptr = ptr.nodptr[0];
		d++;
	}
	this.contex.font = this.Tfont;
	while (true) {
		for (var i=0, len=ptr.keyval.length; i<len; i++) {
			w += this.contex.measureText(ptr.keyval[i]).width + 4;
		}
		w += ((this.maxkey - ptr.keyval.length) * 9) + 1;
		if (ptr.nextLf === null) break;
		ptr = ptr.nextLf;
		w += this.hPad;
	}

	// Prep canvas
	this.canvas.width = w;
	this.canvas.height = this.ypos(d) + this.Tsize + 20;
	this.contex.clearRect(0, 0, this.canvas.width, this.canvas.height);
	this.contex.font = this.Tfont;
	this.contex.lineWidth = 1;
	this.contex.strokeStyle = this.Pline;
};

tree.prototype.ypos = function (lvl) {
	var oneRow = this.Tsize + 13 + this.vPad;
	return (10 + (lvl * oneRow));
};

tree.prototype.drawNode = function (ptr,lvl) {
	var ret = [];
	var y = this.ypos(lvl);
	if (ptr.isLeaf()) {
		ret[0] = this.curLeft;
		ret[1] = this.drawLeaf(ptr,y);
		return ret;
	}
	var KL=[], KR=[];
	for (var i=0, len=ptr.nodptr.length; i<len; i++) {
		ret = this.drawNode(ptr.nodptr[i],lvl+1);
		KL[i] = ret[0];
		KR[i] = ret[1];
	}

	var cA = this.contex;
	var h = this.Tsize;
	var x, p, xb, yb, w=0;
	for (var i=0, len=ptr.keyval.length; i<len; i++) {
		w += cA.measureText(ptr.keyval[i]).width + 4;
	}
	w += ((this.maxkey - ptr.keyval.length) * 10) + 1;
	x = Math.floor((KR[KR.length-1]-KL[0]-w)/2) + KL[0];
	ret[0] = x;
	ret[1] = x+w;

	yb = this.ypos(lvl+1);
	cA.beginPath();
	for (var i=0, len=this.maxkey+1; i<len; i++) {
		w = (i >= ptr.keyval.length) ? 6 : cA.measureText(ptr.keyval[i]).width;
		cA.fillStyle = this.Nline;
		if (i < this.maxkey)
			cA.fillRect(x, y, w+5, h+13);
		else
			cA.fillRect(x, y+h+5, w+5, 8);
		cA.fillStyle = this.Nfill;
		if (i < this.maxkey)
			cA.fillRect(x+1, y+1, w+3, h+4);
		cA.fillRect(x+1, y+h+6, w+3, 6);
		if (i < ptr.keyval.length) {
			cA.fillStyle = this.Tline;
			cA.fillText(ptr.keyval[i],x+2,y+h+2);
		}
		if (i < ptr.nodptr.length) {
			cA.fillStyle = this.Pfill;
			p = Math.floor((w-4)/2);
			cA.fillRect(x+p+2, y+h+8, 4, 4);
			xb = Math.floor((KR[i]-KL[i])/2) + KL[i];
			cA.moveTo(x+p+4, y+h+13);
			cA.lineTo(xb, yb);
		}
		x += w+4;
	}
	cA.stroke();
	return ret;
};

tree.prototype.drawLeaf = function(ptr,y) {
	var cA = this.contex;
	var x = this.curLeft;
	var h = this.Tsize;
	var w;
	var sx = -1;
	for (var i=0, len=this.maxkey; i<len; i++) {
		if (ptr !== null && ptr == this.leaf && i == this.item) sx = x;
		w = (i >= ptr.keyval.length) ? 5 : cA.measureText(ptr.keyval[i]).width;
		cA.fillStyle = this.Lline;
		cA.fillRect(x, y, w+5, h+10);
		cA.fillStyle = this.Lfill;
		cA.fillRect(x+1, y+1, w+3, h+8);
		x += w+4;
	}
	if (sx != -1) {
		w = cA.measureText(ptr.keyval[this.item]).width;
		cA.fillStyle = this.Cline;
		cA.fillRect(sx, y, w+5, h+10);
		cA.fillStyle = this.Cfill;
		cA.fillRect(sx+1, y+1, w+3, h+8);
	}
	cA.fillStyle = this.Tline;
	sx = this.curLeft;
	for (var i=0, len=ptr.keyval.length; i<len; i++) {
		w = cA.measureText(ptr.keyval[i]).width;
		cA.fillText(ptr.keyval[i],sx+2,y+h+4);
		sx += w+4;
	}
	this.curLeft = x+this.hPad;
	return x;
};
