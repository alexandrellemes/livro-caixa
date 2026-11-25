<?php

use App\Support\Session;

require_once __DIR__ . '/../partials/header.php';

?>
    <div class="form-group row">
        <label class="control-label col-md-4">
            <h2 style="margin:5px">Livro Caixa - <?= htmlspecialchars(APP_NAME) ?></h2>
        </label>
        <label class="control-label col-md-8 text-right">
            <a href="?mes=<?= date('m') ?>&ano=<?= date('Y') ?>">Hoje:<strong> <?= date('d') ?> de <?= mostraMes((int) date('m')) ?> de <?= date('Y') ?></strong></a>
        </label>
    </div>
    <hr>
    <div class="form-group row">
        <label class="control-label col-md-2">
            <select onchange="location.replace('?mes=<?= $data['mes'] ?>&ano='+this.value)">
                <?php for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++): ?>
                    <option value="<?= $i ?>" <?= ($i == (int) $data['ano']) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <label class="control-label col-md-10 text-justify">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <span align="center" style="padding-right:5px; padding-left:5px;">
                    <a href="?mes=<?= $i ?>&ano=<?= $data['ano'] ?>" style="<?= ((int) $data['mes'] === $i) ? 'font-size:20px; font-weight:bold; color: lightblue;' : '' ?>">
                        <?= mostraMes($i) ?>
                    </a>
                </span>
            <?php endfor; ?>
        </label>
    </div>
    <hr>

    <div>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#AbaMovimento" role="tab" data-toggle="tab">Movimento</a></li>
            <li role="presentation"><a href="#AbaCategorias" role="tab" data-toggle="tab">Categorias</a></li>
            <li role="presentation"><a href="#AbaResultado" role="tab" data-toggle="tab">Resultado</a></li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane active" id="AbaMovimento">
            <br>
            <h2><?= mostraMes((int) $data['mes']) ?>/<?= $data['ano'] ?></h2>
            <br>
            <div class="table-responsive">
                <table class="table" cellpadding="10" cellspacing="0" width="auto" align="center">
                    <tr>
                        <td align="right">
                            <a href="javascript:;" onclick="abreFecha('add_cat')" class="bnt">[+] Adicionar Categoria</a>
                            <a href="javascript:;" onclick="abreFecha('add_movimento')" class="bnt"><strong>[+] Adicionar Movimento</strong></a>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <?php $flashSuccess = Session::getFlash('success'); if ($flashSuccess): ?><div style="padding:5px; background-color:#FF6; text-align:center; color:#030"><strong><?= htmlspecialchars((string) $flashSuccess) ?></strong></div><?php endif; ?>
                            <?php $flashErrors = Session::getFlash('errors'); if ($flashErrors): ?><div style="padding:5px; background-color:#900; text-align:center; color:#FFF"><strong><?= htmlspecialchars(implode(' ', (array) $flashErrors)) ?></strong></div><?php endif; ?>
                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok'] == 2): ?><div style="padding:5px; background-color:#FF6; text-align:center; color:#030"><strong>Categoria removida com sucesso!</strong></div><?php endif; ?>
                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok'] == 1): ?><div style="padding:5px; background-color:#FF6; text-align:center; color:#030"><strong>Categoria cadastrada com sucesso!</strong></div><?php endif; ?>
                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok'] == 3): ?><div style="padding:5px; background-color:#FF6; text-align:center; color:#030"><strong>Categoria alterada com sucesso!</strong></div><?php endif; ?>
                            <?php if (isset($_GET['ok']) && $_GET['ok'] == 1): ?><div style="padding:5px; background-color:#FF6; text-align:center; color:#030"><strong>Movimento cadastrado com sucesso!</strong></div><?php endif; ?>
                            <?php if (isset($_GET['ok']) && $_GET['ok'] == 2): ?><div style="padding:5px; background-color:#900; text-align:center; color:#FFF"><strong>Movimento removido com sucesso!</strong></div><?php endif; ?>
                            <?php if (isset($_GET['ok']) && $_GET['ok'] == 3): ?><div style="padding:5px; background-color:#FF6; text-align:center; color:#030"><strong>Movimento alterado com sucesso!</strong></div><?php endif; ?>

                            <div style="background-color:#F1F1F1; padding:10px; border:1px solid #999; margin:5px; display:none" id="add_cat">
                                <h3>Adicionar Categoria</h3>
                                <form method="post" action="?mes=<?= $data['mes'] ?>&ano=<?= $data['ano'] ?>">
                                    <input type="hidden" name="acao" value="2" />
                                    Nome: <input type="text" name="nome" size="20" maxlength="50" />
                                    <br /><br />
                                    <input type="submit" class="input" value="Enviar" />
                                </form>
                            </div>

                            <div style="background-color:#F1F1F1; padding:10px; border:1px solid #999; margin:5px; display:none" id="add_movimento">
                                <h3>Adicionar Movimento</h3>
                                <?php if (empty($data['categorias'])): ?>
                                    <p>Adicione ao menos uma categoria.</p>
                                <?php else: ?>
                                    <form method="post" action="?mes=<?= $data['mes'] ?>&ano=<?= $data['ano'] ?>">
                                        <input type="hidden" name="acao" value="1" />
                                        <strong>Data:</strong><br />
                                        <input type="text" name="data" size="11" maxlength="10" value="<?= date('d') ?>/<?= $data['mes'] ?>/<?= $data['ano'] ?>" />
                                        <br /><br />
                                        <strong>Tipo:</strong><br />
                                        <label for="tipo_receita" style="color:#030"><input type="radio" name="tipo" value="1" id="tipo_receita" /> Receita</label>
                                        <label for="tipo_despesa" style="color:#C00"><input type="radio" name="tipo" value="0" id="tipo_despesa" /> Despesa</label>
                                        <br /><br />
                                        <strong>Categoria:</strong><br />
                                        <select name="categoria">
                                            <?php foreach ($data['categorias'] as $categoria): ?>
                                                <option value="<?= (int) $categoria['id'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <br /><br />
                                        <strong>Descrição:</strong><br />
                                        <input type="text" name="descricao" size="100" maxlength="255" />
                                        <br /><br />
                                        <strong>Valor:</strong><br />
                                        <input type="text" name="valor" id="valor" size="8" maxlength="10" data-symbol="R$ " data-thousands="." data-decimal="," />
                                        <br /><br />
                                        <input type="submit" class="input" value="Enviar" />
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <br>
                <table class="table" cellpadding="5" cellspacing="0" width="auto" align="center">
                    <tr>
                        <td colspan="2">
                            <h2>Movimentos deste Mês</h2>
                            <div style="float:right; text-align:right">
                                <form name="form_filtro_cat" method="get" action="">
                                    <input type="hidden" name="mes" value="<?= $data['mes'] ?>">
                                    <input type="hidden" name="ano" value="<?= $data['ano'] ?>">
                                    Filtrar por categoria:
                                    <select name="filtro_cat" onchange="form_filtro_cat.submit()">
                                        <option value="">Todas</option>
                                        <?php foreach ($data['categorias'] as $categoria): ?>
                                            <option value="<?= (int) $categoria['id'] ?>" <?= ((string) ($categoria['id'] ?? '') === (string) $data['filtroCategoria']) ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="submit" value="Filtrar" class="botao"/>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php $cont = 0; foreach ($data['movimentos'] as $row): $cont++; $categoriaNome = $data['categoriasById'][(int) $row['categoria_id']] ?? 'Sem categoria'; ?>
                        <tr style="background-color:<?= ($cont % 2 === 0) ? '#F1F1F1' : '#E0E0E0' ?>">
                            <td align="center" width="15"><?= (int) $row['dia'] ?></td>
                            <td><?= htmlspecialchars($row['descricao']) ?> <em>(<a href="?mes=<?= $data['mes'] ?>&ano=<?= $data['ano'] ?>&filtro_cat=<?= (int) $row['categoria_id'] ?>"><?= htmlspecialchars($categoriaNome) ?></a>)</em></td>
                            <td align="right"><strong style="color:<?= ((int) $row['tipo'] === 0) ? '#C00' : '#030' ?>"><?= ((int) $row['tipo'] === 0) ? '-' : '+' ?><?= formata_dinheiro((float) $row['valor']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" align="right">
                            <strong style="font-size:22px; color:<?= ($data['resumoMensal']['resultado'] < 0) ? '#C00' : '#030' ?>"><?= formata_dinheiro((float) $data['resumoMensal']['resultado']) ?></strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="tab-pane" id="AbaCategorias">
            <br>
            <h3>Categorias</h3>
            <ul>
                <?php foreach ($data['categorias'] as $categoria): ?>
                    <li><?= htmlspecialchars($categoria['nome']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="tab-pane" id="AbaResultado">
            <br>
            <fieldset>
                <legend class="panel-title">Entradas e Saídas deste mês</legend>
                <table class="table">
                    <tr><td><span style="font-size:18px; color:#030">Entradas:</span></td><td align="right"><span style="font-size:18px; color:#030"><?= formata_dinheiro((float) $data['resumoMensal']['entradas']) ?></span></td></tr>
                    <tr><td><span style="font-size:18px; color:#C00">Saídas:</span></td><td align="right"><span style="font-size:18px; color:#C00"><?= formata_dinheiro((float) $data['resumoMensal']['saidas']) ?></span></td></tr>
                    <tr><td colspan="2"><hr size="1" /></td></tr>
                    <tr><td><strong style="font-size:22px; color:<?= ($data['resumoMensal']['resultado'] < 0) ? '#C00' : '#030' ?>">Resultado:</strong></td><td align="right"><strong style="font-size:22px; color:<?= ($data['resumoMensal']['resultado'] < 0) ? '#C00' : '#030' ?>"><?= formata_dinheiro((float) $data['resumoMensal']['resultado']) ?></strong></td></tr>
                </table>
            </fieldset>
            <br>
            <fieldset>
                <legend class="panel-title">Balanço Geral</legend>
                <table class="table">
                    <tr><td><span style="font-size:18px; color:#030">Entradas:</span></td><td align="right"><span style="font-size:18px; color:#030"><?= formata_dinheiro((float) $data['resumoGeral']['entradas']) ?></span></td></tr>
                    <tr><td><span style="font-size:18px; color:#C00">Saídas:</span></td><td align="right"><span style="font-size:18px; color:#C00"><?= formata_dinheiro((float) $data['resumoGeral']['saidas']) ?></span></td></tr>
                    <tr><td colspan="2"><hr size="1" /></td></tr>
                    <tr><td><strong style="font-size:22px; color:<?= ($data['resumoGeral']['resultado'] < 0) ? '#C00' : '#030' ?>">Resultado:</strong></td><td align="right"><strong style="font-size:22px; color:<?= ($data['resumoGeral']['resultado'] < 0) ? '#C00' : '#030' ?>"><?= formata_dinheiro((float) $data['resumoGeral']['resultado']) ?></strong></td></tr>
                </table>
            </fieldset>
        </div>
    </div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
