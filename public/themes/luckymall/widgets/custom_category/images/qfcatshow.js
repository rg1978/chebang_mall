var qfcatShow = new Class({
	Implements: [Options, Events],
	options:{
		'container':'categorys',//主容器
		'menuItem':'.item',//菜单项的CSS名称
		'subContainer': '.dorpdown-layer',//子菜单容器的css名称，如果是用的ID，可以填（#id）
		'subMenuPrefix':'category-item-'//子菜单项ID前缀
	},

	initialize: function(options){
		this.setOptions(options);
		var ops = this.options;
		var hd = this.handle = document.id(ops['container']);
		this.lastItem = null;
		this.items = hd.getElements(ops['menuItem']);
		this.subContainer = hd.getElement(ops['subContainer']);
		this.subConTop = parseInt(this.subContainer.getStyle('top'));
		this.top = hd.getCoordinates().top;
		this.bounds = {
			'leave': this.leave.bind(this),
			'over': this.over.bind(this)
		}

		this.attach();
	},

	attach: function(){
		this.handle.addEvent('mouseleave', this.bounds.leave);
		this.items.each(function(item){
			this.hideSub(item);
			item.addEvent('mouseenter', this.bounds.over);
		}, this);
	},

	over: function(el){
		this.leave();
		this.showSub(el.event.target);
	},

	showSub: function(item){
		var id = item.get('data-index');
		var subId = this.options['subMenuPrefix'] + id;
		var sub = document.id(subId);
		if(!sub) return;
		var subCon = this.subContainer;
		sub.setStyle('display', 'block');
		if(subCon){
			subCon.setStyle('display','block');
			var itemBottom = item.getCoordinates().bottom;
			var subHeight = sub.getCoordinates().height;

			var scrollTop = document.body.scrollTop;
			var subTop = this.subConTop;
			//如果出现了滚动条
			//如果滚动条的高度已经超过了基准线
			if(scrollTop >= this.top + this.subConTop){
				subTop = scrollTop - this.top;
			}

			//先判断当前的高度加上容器的高度是否超过了当前Item的底部，如果不到底部，优先底部对齐
			if(itemBottom - Math.max(scrollTop, this.top + this.subConTop) > subHeight)
				subTop = itemBottom - this.top - subHeight - parseInt(subCon.getStyle('border'));

			subCon.setStyle('top', subTop);
		}
		this.lastItem = item;
		this.fireEvent('over', item);
	},

	leave: function(){
		var lastItem = this.lastItem;
		if(lastItem){
			this.hideSub(lastItem);
			this.fireEvent('leave', lastItem);
			this.lastItem = null;
		}
		this.subContainer.setStyle('display', 'none');
	},

	hideSub: function(item){
		var id = item.get('data-index');
		var subId = this.options['subMenuPrefix'] + id;
		var sub = document.id(subId);
		if(!sub) return;
		sub.setStyle('display', 'none');
	}
});