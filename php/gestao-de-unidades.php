<?php

require_once("custom/php/common.php");

//verificacoes comuns
if(!is_user_logged_in())
{
   die("Tem que fazer o login");
}
// else
// {
//     echo 'Utilizador tem sessão iniciada'.'<br>';
// }
 if(!current_user_can( "manage_unit_types"))
 {
     die("Não tem autorização para aceder a esta página");
 }
 // else
 // {
 //     echo 'Autorização concedida'.'<br>';
 // }

$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME); // Ligacao a BD

$query = "SELECT * FROM attr_unit_type"; // Vai buscar Tuplos da DB
$result = mysqli_query($ligacao, $query); //Executa a Query

 if(!$ligacao)
 {
     echo '<strong>Erro ao ligar à Base de dados</strong>'.mysqli_error($ligacao);
 }
 // else
 // {
 //     echo 'Ligação realizada com sucesso'.'<br>';
 // }

//Caso a Base de dados esteja Vazia (!0>0) == (!False) == (True)
//if(!mysqli_num_rows($result) > 0))
//{
//	echo "Não existem tipos de unidades" . '<br>';
//}
//else
//{
	// Estrutura da tabela com estilo Row strip ?>
	<table class = "mytable" border width = "100%">
    <thead>
        <tr>
            <th><b>id</b></th>
            <th><b>unidade</b></th>
        </tr>
    </thead>
    <tbody>
<?php
	// Ciclo de busca de tuplos instancia a instancia por meio do fetch_assoc
	while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?=$row["id"]?></td>
            <td><?=$row["name"];?></td>
        </tr> <?php
	} //Fim de tabela quando o ciclo terminar, fetch_assoc ja nao tem mais instancias para ler
	?>
    </tbody>
    </table>


	<!-- Formulario para insercao de unidades (HTML) -->
	<h3>Gestão de Unidades - Introducao</h3>
		<form method="POST">
			<table>
                <tr>
                     <strong><td>Nome:</td></strong>
                    <td><input type="text" name="Nome" size="10"></td>
					<input type="hidden" name="estado" value="inserir"/>
                </tr>
                <tr>
                    <td colspan="2" align="center"><input type="Submit" value="Inserir tipo de unidade" /></td>
                </tr>
            </table>
		</form>


	<!-- Formulario para Validacao de insercoes de unidadescao  -->
<?php
if($_REQUEST["estado"] == "inserir" && empty($_REQUEST["Nome"])) //Verifica se tem alguma Unidade (Nome) a Inserir
{
	echo "<strong>ATENÇÃO: </strong><i>Nome da Unidade é obrigatória.</i>";
	volta_atras();
	die("");
}
if ($_REQUEST["estado"] == "inserir")
	{
	//Quando o indice estado se encontrar a inserir e houver dados a inserir
	$unit_nome = $_REQUEST["Nome"];
	$result_unit_nome = mysqli_real_escape_string($ligacao,$unit_nome); //Confere a seguranca dos carateres na string para que possa ser feita a query sobre ela
	$query_insert_unit = "INSERT INTO attr_unit_type (name) VALUES ('$result_unit_nome')"; //query a inserir
	$result_inserir = mysqli_query($ligacao, $query_insert_unit); //Insert na BD
?>
	<h3>Gestão de Unidades - Inserção</h3>
<?php

if (!$result_inserir) // se não conseguir inserir
{
	die("Ocorreu um erro ao introduzir a query");
}
else // caso consiga inserir
    {
      echo "<i> Inseriu os dados de novo tipo de unidade com sucesso.</i>";
    }
    ?>
    <p style="color:grey" align="left"><i>Clique em <a href="gestao-de-unidades"><strong>Continuar</strong></a> para avançar.</i></p>

    <?php
	}
?>
