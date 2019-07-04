function calcPrice(obj, id, originalPrice, ajax=false)
{
	var qty = obj.value;

	var pHT = originalPrice;

	pHT = (pHT * qty);
	var pTTC =  pHT * 1.2;

	if(ajax) {
		pTTC = pHT;
		$.post('/cart/update', {id: ajax, qty: qty}, function(data) {
			if(data == 'OK') {
				updateCart();	
			}
		});
	}
	else {
		document.getElementById('PHT_'+id).innerHTML = String(pHT.toFixed(2)).replace('.', ',')+"€";
	}
	document.getElementById('PTTC_'+id).innerHTML = String(pTTC.toFixed(2)).replace('.', ',')+"€";
}

function deleteCartLine(id) {
	$.post('/cart/deleteLine', {id: id}, function(data) {
		if(data == 'OK') {
			$('#line_'+id).remove();
			updateCart();
		}
	});
}

function getProductsModal(title, img, content, price, id) {
	$('#modal-message').removeAttr('class').text('');

	$('#modal-title').text(title);
	$('#modal-body-img').attr('src', img).attr('alt', title);
	$('#modal-body').text(content);
	$('#modal-body-price').text(price+'€');
	$('#product_id').attr('onclick', 'addToCart('+id+')');
}

function addToCart(id) {
	$.post('/beer/find', {id: id}, function(data) {
		$('#modal-message').attr('class', 'alert alert-success').text(data);
		$.get('/cart/getCart', {}, function(nbProduct) {
			$('#cartNumber').text(nbProduct);
		})
	});
}

function updateCart() {
	PTHT = 0;
	$('.HT').each(function(e) {
		var price = $('.HT')[e].innerHTML;
		PTHT += parseFloat(price.split('€')[0].replace(',', '.'));
	});
	var PTTTC = PTHT*1.2;
	
	$('#PTHT').html(String(PTHT.toFixed(2)).replace('.', ',')+"€");
	$('#TVA').html(String((PTHT * 0.2).toFixed(2)).replace('.', ',')+"€")
	$('#PTTTC').html(String(PTTTC.toFixed(2)).replace('.', ',')+"€");
}