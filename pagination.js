

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
			case 'breed':
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
			case 'breed':
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
$( "#slider-range" ).slider({
      range: true,
      min: 200,
      max: 20000,
      values: [ 1948 , 5247 ],
      slide: function( event, ui ) {
        $( "#amount" ).val( "₹" + ui.values[ 0 ] + " - ₹" + ui.values[ 1 ] );
		stprice=ui.values[ 0 ];
			enprice=ui.values[ 1 ];
			csp(stprice,enprice);
      }
    });
    $( "#amount" ).val( "₹" + $( "#slider-range" ).slider( "values", 0 ) +
      " - ₹" + $( "#slider-range" ).slider( "values", 1 ) );
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
								url:encodeURI('Classes/crazybean.php'),
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
										plugin.html(msg.result);
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
		
