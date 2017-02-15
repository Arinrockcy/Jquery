// JavaScript Document

(function($) {  

 $.fn.extend({
        ajaxPaging: function(options) {
			$('.loading-gif').show();
			var plugin=$(this);
            var defaults = {
                value: 1,
                defaultfunction: plugin.attr('for'),
				paginationfunction:plugin.attr('to'),
				condtion:'Product.category_Id'
            };         
options = $.extend(defaults, options);
	
	
	var pf=null;
	
	var rt=null;
	var fl=null;
	var st;
	var sorting=null, brand=null, style=null,stprice=null,enprice=null;
	
	
	if(pf!=1)
		{
		ap(options.defaultfunction,options.value,0,sorting,brand,style,stprice,enprice);
		
		}
		else
		{ap(options.paginationfunction,options.value,0,sorting,brand,style,stprice,enprice);
			}
			
			
			
	$('body').on('click','.load_Next',function(){
		if(fl==1)
			{
		plugin.html(rt);
		fl=null;
			}
		srt=$(this).attr('next');
		ap(options.paginationfunction,options.value,srt,sorting,brand,style,stprice,enprice);
	});
	$('body').on('click','.sort-paging',function(){
		sorting=$(this).attr('type');
		ap(options.defaultfunction,options.value,0,sorting,brand,style,stprice,enprice);
		});
	$('body').on('click','.fliter-paging-ckeck:checked',function(){
		
		
		switch($(this).attr('for'))
		{
			case 'brand':
			brand=$(this).val();
			fpc(brand,null);
			break;
			}
		});
$('body').on('click','.fliter-paging-ckeck',function(){
		if(!$(this).is(':checked'))
		{
		switch($(this).attr('for'))
		{
			case 'brand':
			brand=$(this).val();
			fpc(null,style);
			break;
			}
		}
		});		
		function fpc(br,sty)
		{
			ap(options.defaultfunction,options.value,0,sorting,br,sty,stprice,enprice);
			}	
$('body').on('change','.selectpicker',function(){
	
	switch($(this).attr('for'))
		{
			case 'loadMprice':
			stprice=$(this).val();
			enprice=$(".selectpicker:eq(1) option:selected").val();
			csp(stprice,enprice);
			break;
			case 'loadmaxprice':
			enprice=$(this).val();
			stprice=$(".selectpicker:eq(0) option:selected").val();
			csp(stprice,enprice);
			break;
			}
	});	
	function csp(minp,maxp)
	{
		ap(options.defaultfunction,options.value,0,sorting,brand,style,minp,maxp);
		}			
	$('.pre').click(function(){
		st=$(this).attr('next');
			ap(options.paginationfunction,options.value,st,sorting);
		
	});	
	function ap(to,data,st,sor,brd,stle,minp,maxp)
	{
			$.ajax({
								url:encodeURI('classes/dy_service.php'),
								dataType: 'json',  // what to expect back from the PHP script, if anything
         		       			cache: false,
                				type:'post',
							 	data:{'to':to,'key':data,'start':st,'sorting':sor,'brnd':brd,'stle':stle,'minprice':minp,'maxprice':maxp},
								success:function(msg) {
									
									if(fl==1)
									{
							    rt=msg.result;
								fl=null;
									}
									else
									{
										$('#show_start').html(msg.show1);
										$('#show_start2').html(msg.show2);
										plugin.html(msg.result);
									
									}
									if(pf!=1)
									{
										$('#show_total').html(msg.total);
										$('.pagination').html(msg.row);
										pf=1;
										}
										$('.loading-gif').hide();
									 	}
										
										});	
	}
	
	
	
	
	
	
	
	
	
	
	
	
		}
		
		});
		
		})(jQuery);
		