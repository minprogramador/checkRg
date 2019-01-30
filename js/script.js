var URL = "http://localhost:7777/";

showAllData();

function newData() {	
	showAllData();
}


$('#SaveBtn').click(function() {
	if ($('#id').val() != '')
		updateData();
	else
	return false;
});


function showAllData() {
	$('#ListData').html("");
	$.ajax({
		type: 'POST',
		data: {config: "list"},
		url: URL,
		dataType: "json",
		success: setDataList
	});
}

function selectData(id_customer) {
	$.ajax({
		type: 'GET',
		url: URL + '/' + id_customer,
		dataType: "json",
		success: function(customer){
			$('#DeleteBtn').show();
			console.log('selectData success: ' + customer.id_customer);
			setDetail(customer);
		}
	});
}


function updateData() {

	var url_path = $('#url_path').val();

	var api_cpf = $('#api_cpf').val();
	var proxy = $('#proxy').val();
	var debug = $('#debug').val();
	var ip_debug = $('#ip_debug').val();
	var status = $('#status').val();
	var timeout = $('#timeout').val();

	$.ajax({
		type: 'POST',
		url: URL,
		data: {url_path: url_path, api_cpf: api_cpf, proxy: proxy, debug: debug, ip_debug:ip_debug, status:status, timeout:timeout, config: "edit" },
		dataType: "json",
		success: function(data, status, jqXHR){
			alert('config alterada com sucesso!');
			showAllData();
		},
		error: function(jqXHR, status, errorThrown){
			alert('updateData error: ' + status);
		}
	});
}


function setDetail(customer) {
	$('#id').val(customer.id_customer);
	$('#nama_customer').val(customer.nama_customer);
	$('#alamat').val(customer.alamat);
	$('#telepon').val(customer.telepon);
	$('#tempat_lahir').val(customer.tempat_lahir);
	$('#tgl_lahir').val(customer.tgl_lahir);
}

function setDataList(data) {

	$('#url_path').val(data.url_path);
	$('#api_cpf').val(data.api_cpf);
	$('#proxy').val(data.proxy);
	$('#debug').val(data.debug);
	$('#ip_debug').val(data.ip_debug);
	$('#status').val(data.status);
	$('#timeout').val(data.timeout);

}

function parseToJson() {
	var data = JSON.stringify({
		"url_path": $('#url_path').val(), 
		"api_cpf": $('#api_cpf').val(), 
		"proxy": $('#proxy').val(),
		"debug": $('#debug').val(),
		"ip_debug": $('#ip_debug').val(),
		"status": $('#status').val(),
		"timeout": $('#timeout').val()
		});
	return data;
}
