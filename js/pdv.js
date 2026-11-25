/* PDV: máscara de valores, cálculo de troco e validação antes do envio. */
$(function () {
    var $valor = $('#pdv-valor');
    var $pago = $('#pdv-pago');
    var $troco = $('#pdv-troco');
    var $trocoBox = $('#pdv-troco-box');

    if (!$valor.length) {
        return;
    }

    $valor.maskMoney();
    $pago.maskMoney();

    // "R$ 1.234,56" -> 1234.56
    function paraNumero(texto) {
        var limpo = String(texto || '').replace(/[^\d,]/g, '').replace(',', '.');
        var numero = parseFloat(limpo);
        return isNaN(numero) ? 0 : numero;
    }

    function formaSelecionada() {
        return $('input[name="forma"]:checked').val();
    }

    function atualizarTroco() {
        var valor = paraNumero($valor.val());
        var pago = paraNumero($pago.val());
        var diferenca = pago - valor;
        var texto = Math.abs(diferenca).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});

        if (!pago) {
            $troco.text('R$ 0,00').css('color', '');
        } else if (diferenca >= 0) {
            $troco.text(texto).css('color', 'green');
        } else {
            $troco.text('faltam ' + texto).css('color', '#C00');
        }
    }

    function atualizarForma() {
        // Troco só faz sentido para recebimento em dinheiro.
        $trocoBox.toggle(formaSelecionada() === 'dinheiro');
    }

    $valor.add($pago).on('change paste keyup', atualizarTroco);
    $('input[name="forma"]').on('change', atualizarForma);
    // Bootstrap 3 (data-toggle="buttons") nem sempre dispara "change" no radio.
    $('[data-toggle="buttons"] .btn').on('click', function () {
        setTimeout(atualizarForma, 0);
    });

    $('#frmPDV').on('submit', function (evento) {
        if (paraNumero($valor.val()) <= 0) {
            evento.preventDefault();
            alert('Informe o valor do lançamento.');
            $valor.focus();
        }
    });

    atualizarForma();
    atualizarTroco();
    $valor.focus();
});
