function mostra_menu(element,event){
el=document.getElementById(element);

if (el.style.display=='none'){

	if(document.selection)
		el.style.display='';
	else
		el.style.display='inline';

	el.style.top=event.offsetY;
	el.style.left=event.offsetX;

}else{

	el.style.display='none';

}

}

function insereTag (element,tag) {
el=document.getElementById(element);


if(document.selection){//IE

if (tag =='b' || tag =='i' || tag =='u')
		var newText='['+tag+'][/'+tag+']';
	else
		var newText=" "+tag+" ";

	el.value+=newText;

}else{//FF

	var selectedText=document.selection?document.selection.createRange().text:el.value.substring(el.selectionStart,el.selectionEnd);// IE:Moz

	if (tag =='b' || tag =='i' || tag =='u')
		var newText='['+tag+']'+selectedText+'[/'+tag+']';
	else
		var newText=" "+tag+" ";

	el.value=el.value.substring(0,el.selectionStart)+newText+el.value.substring(el.selectionEnd,el.value.length);

}

}

function flash(file, width, height){
    document.write("<object  classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0' width='" + width + "' height='" + height + "'>");
    document.write("<param name='movie' value='" + file + "'>");
    document.write("<param name='quality' value='high'>");
	document.write("<param name='wmode' value='transparent' />");
    document.write("<embed  src='" + file + "' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' width='" + width + "' height='" + height + "' wmode='transparent'></embed>");
    document.write("</object>");
}

function checar_caps_lock(ev) {
	var e = ev || window.event;
	codigo_tecla = e.keyCode?e.keyCode:e.which;
	tecla_shift = e.shiftKey?e.shiftKey:((codigo_tecla == 16)?true:false);
	if(((codigo_tecla >= 65 && codigo_tecla <= 90) && !tecla_shift) || ((codigo_tecla >= 97 && codigo_tecla <= 122) && tecla_shift)) {
		document.getElementById('aviso_caps_lock').style.display = 'block';
	}
	else {
		document.getElementById('aviso_caps_lock').style.display = 'none';
	}
}


function abreDiv(valor,retorna){
	divFundo=document.getElementById('fundoPreto');
	divAviso=document.getElementById('div_aviso');

	divFundo.style.display='block';
	divAviso.style.display='block';

	divAviso.style.top='40%';
	divAviso.style.left='35%';
	divAviso.style.width='350px';
	divAviso.style.height='50px';

	abreArquivoAjax('ajax/usuario/mostra_rs.php?valor='+valor,'div_aviso',retorna,true)
}

function fechaAviso(){
	divFundo=document.getElementById('fundoPreto');
	divAviso=document.getElementById('div_aviso');
	divFundo.style.display='none';
	divAviso.style.display='none';
}

function abreFecha(id){

if (document.getElementById(id).style.display=='')
	document.getElementById(id).style.display='none';
else
	document.getElementById(id).style.display='';
}

function abre(id){
	document.getElementById(id).style.display='block';
}

function fecha(id){
	document.getElementById(id).style.display='none';
}

function abre2(id){
	document.getElementById(id).style.display='';
}


function alternaClasse(id,classe1,classe2){
if (document.getElementById(id).className==classe1)
	document.getElementById(id).className=classe2;
else
	document.getElementById(id).className=classe1;
}

function select(theField) {
var tempval=eval("document."+theField);
tempval.focus();
tempval.select();
}

function abreSubTab(flag){

	for (i=1;i<=12;i++){
		fecha("subtab"+i);
		document.getElementById("itemtab"+i).className="";
	}

	abre("subtab"+flag);
	document.getElementById("itemtab"+flag).className="current";

}

/////

function abrirTermos(tipo,retorna){
	window.open(retorna+'termos?tipo='+tipo, '', "status=no, width=600, height=500, scrollbars=yes");
}


////

function emoticon(smilie,div){
	campo=document.getElementById(div);
	campo.value=campo.value+" "+smilie;
}


/////////

function checa_seguranca(pass, campo){
		var senha = pass;
		var entrada = 0;
		var resultadoado;

		if(senha.length < 9){
				entrada = entrada - 1;
		}

		if(!senha.match(/[a-z_]/i) || !senha.match(/[0-9]/)){
				entrada = entrada - 1;
		}

		if(!senha.match(/\W/)){
				entrada = entrada - 1;
		}

		if(entrada == 0){
				resultado = 'A Segurança de sua senha é: <font color=\'#388000\'><strong>EXCELENTE</strong></font>';
		} else if(entrada == -1){
				resultado = 'A Segurança de sua senha é: <font color=\'#0037FF\'><strong>MÉDIA</strong></font>';
		} else if(entrada == -2){
				resultado = 'A Segurança de sua senha é: <font color=\'#FFB200\'><strong>BAIXA</strong></font>';
		} else if(entrada == -3){
				resultado = 'A Segurança de sua senha é: <font color=\'#FF0000\'><strong>MUITO BAIXA</strong></font>';
		}

		document.getElementById(campo).innerHTML = resultado;

		return;
}
///////////////////
//mascaras
//////////////////

function formatar(src, mask, evtKeyPress)
{
  var tecla = window.event ? evtKeyPress.keyCode : evtKeyPress.which;

  if (tecla != 8) //só força a escrita da máscara se a tecla pressionada NÃO for o BACKSPACE
  {
    var i = src.value.length;
    var saida = mask.substring(0,1);
    var texto = mask.substring(i)
    if (texto.substring(0,1) != saida)
    {
      src.value += texto.substring(0,1);
    }
  }
}

function sonumeros(e,args)
    {
        if (document.all){var evt=event.keyCode;}
        else{var evt = e.charCode;}
        var valid_chars = '0123456789'+args;
        var chr= String.fromCharCode(evt);
        if (valid_chars.indexOf(chr)>-1 ){return true;}
        if (valid_chars.indexOf(chr)>-1 || evt < 9){return true;}
        return false;
    }

function troca(e,args)
    {
        if (document.all){var evt=event.keyCode;}
        else{var evt = e.charCode;}
        var valid_chars = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM'+args;
        var chr= String.fromCharCode(evt);
        if (valid_chars.indexOf(chr)>-1 ){return true;}
        if (valid_chars.indexOf(chr)>-1 || evt < 9){return true;}
        return false;
    }

function troca_onblur(campo)
	{
	campo.value = campo.value.toLowerCase();
	var estranha = "������������������������@#$%^&*();._,+=~�` -!%�[]}{:<>";
	var correta =  "aeiouaeiouaeiouaeiouaocy______________________________";
	var retorno = "";
	for(i=0;i<estranha.length;i++)
	{
		for(j=0;j<campo.value.length;j++)
		{
		retorno = campo.value.replace(estranha.substr(i,1),correta.substr(i,1));
		retorno = retorno.replace("_","");
		campo.value = retorno;
		}
	}
}


function addFav(title,url){

 if (window.sidebar) window.sidebar.addPanel(title, url,"");
 else if(window.opera && window.print){
 var mbm = document.createElement('a');
 mbm.setAttribute('rel','sidebar');
 mbm.setAttribute('href',url);
 mbm.setAttribute('title',title);
 mbm.click();
 }
 else if(document.all){window.external.AddFavorite(url, title);}
}

function valida_busca(){
if ((document.getElementById('q_busca_topo').value == "Pesquisar...") || (document.getElementById('q_busca_topo').value == " ")){
document.getElementById('q_busca_topo').focus();
return false;
}
}

/********************************/

function Get_Cookie( check_name ) {
	// first we'll split this cookie up into name/value pairs
	// note: document.cookie only returns name=value, not the other components
	var a_all_cookies = document.cookie.split( ';' );
	var a_temp_cookie = '';
	var cookie_name = '';
	var cookie_value = '';
	var b_cookie_found = false; // set boolean t/f default f

	for ( i = 0; i < a_all_cookies.length; i++ )
	{
		// now we'll split apart each name=value pair
		a_temp_cookie = a_all_cookies[i].split( '=' );


		// and trim left/right whitespace while we're at it
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');

		// if the extracted name matches passed check_name
		if ( cookie_name == check_name )
		{
			b_cookie_found = true;
			// we need to handle case where cookie has no value but exists (no = sign, that is):
			if ( a_temp_cookie.length > 1 )
			{
				cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
			}
			// note that in cases where cookie is initialized but no value, null is returned
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = '';
	}
	if ( !b_cookie_found )
	{
		return null;
	}
}


function Set_Cookie( name, value, expires, path, domain, secure ) {
	// set time, it's in milliseconds
	var today = new Date();
	today.setTime( today.getTime() );
	// if the expires variable is set, make the correct expires time, the
	// current script below will set it for x number of days, to make it
	// for hours, delete * 24, for minutes, delete * 60 * 24
	if ( expires )
	{
		expires = expires * 1000 * 60 * 60 * 24;
	}
	//alert( 'today ' + today.toGMTString() );// this is for testing purpose only
	var expires_date = new Date( today.getTime() + (expires) );
	//alert('expires ' + expires_date.toGMTString());// this is for testing purposes only

	document.cookie = name + "=" +escape( value ) +
		( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + //expires.toGMTString()
		( ( path ) ? ";path=" + path : "" ) +
		( ( domain ) ? ";domain=" + domain : "" ) +
		( ( secure ) ? ";secure" : "" );
}

// this deletes the cookie when called
function Delete_Cookie( name, path, domain ) {
	if ( Get_Cookie( name ) ) document.cookie = name + "=" +
			( ( path ) ? ";path=" + path : "") +
			( ( domain ) ? ";domain=" + domain : "" ) +
			";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}
