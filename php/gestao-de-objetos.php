<?php
require_once("custom/php/common.php");

$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,bitnami_wordpress);


if(!is_user_logged_in()){
  die("Tem que fazer o login");
}
if(!current_user_can("manage_objects"))
{
  die("Não tem autorização para aceder a esta página");
}
else{
	if($_POST['estado'] == ""){

	$query_objeto = "SELECT * FROM object";
	$result_objeto = mysqli_query($ligacao,$query_objeto);

	$query_objetotipo = "SELECT name, id
					FROM obj_type
					ORDER BY id";
	$result_objetotipo = mysqli_query($ligacao,$query_objetotipo);


if (mysqli_num_rows($result_objeto) == 0)
{
	echo "Não há objetos";
}
else{

 ?>
    <table class = "mytable" border width = "100%">
    <thead>
	<tr>
          <th><b> tipo de objeto </b></th>
          <th><b> id </b></th>
          <th><b> nome do objeto </b></th>
          <th><b> estado </b></th>
          <th><b> ação </b></th>
	</tr>
    </thead>
	<tbody>
<?php
	while($tipo_obj = mysqli_fetch_assoc($result_objetotipo))
	{
			$query_restodetabela = "SELECT DISTINCT object.id, object.name, object.state
								FROM object, obj_type
								WHERE object.obj_type_id = ".$tipo_obj["id"]."
								ORDER BY object.id";
			$result_restodetabela = mysqli_query($ligacao,$query_restodetabela);

			$numero_tuplos = mysqli_num_rows($result_restodetabela);

?>

	<tr>
	<td colspan = "1" rowspan = "<?php echo $numero_tuplos ?>">  <?php echo $tipo_obj["name"] ?> </td>

	<?php

			while($restodetabelas = mysqli_fetch_assoc($result_restodetabela)){
	?>

			<td> <?php echo $restodetabelas["id"] ?> </td>
			<td> <?php echo $restodetabelas["name"] ?> </td>
	<?php
			if($restodetabelas["state"] == "active"){
		?>
			<td> ativo </td>
			<td> [editar] [desativar] </td>

<?php
			}
			else
			{
	?>		<td> inativo </td>
			<td> [editar] [desativar] </td>

		<?php
			}
?>
			</tr>
			<?php
			}
	}


?>

	</tbody>
	</table>


	<form name="gestao-de-objetos.php" method="POST">
	<h3><center>Gestão de objetos - introdução</center></h3>
	<label><i> Nome do Objeto: </i></label>
	<input type= "text" name = "nome_objeto" > (obrigatório) <br><br>
	<label><i> Tipo de objeto: </i></label> (obrigatório)<br>

<?php

			$query_objetoformulario = "SELECT name, id
									FROM obj_type";
			$result_objetoformulario = mysqli_query($ligacao,$query_objetoformulario);
			while($objetosformulario = mysqli_fetch_assoc($result_objetoformulario)){
				?>

    <label class = "container">
		<input type="radio" name="tipo_objeto" value="<?php echo $objetosformulario['id']; ?>" > <?php echo $objetosformulario['name']; ?> <br>
    <span class="checkmark"></span>
    </label>
<?php
		}
			?>
			<label><i>Estado:</i></label> (obrigatório) <br>

      <label class = "container">
			<input type = "radio" name = "estado_objeto" value="active">ativo
      <span class="checkmark"></span>
      </label>

      <label class = "container">
			<input type = "radio" name = "estado_objeto" value="inactive">inativo
      <span class="checkmark"></span>
      </label>
      <br>
			<input type = "hidden" name ="estado" value="inserir">
			<input type = "submit" value = "inserir objeto"> <br>

<?php

}
	}
	elseif($_POST['estado'] == "inserir")  {
	?>
		<h3><center>Gestão de objetos - inserção</center></h3>
<?php
		if(empty($_POST['nome_objeto']))
		{
			echo "Não inseriu um nome para o novo objeto!";
?>		<br>
<?php

			  volta_atras();

		}
		elseif(empty($_POST['tipo_objeto']))
		{
			echo "Não selecionou o tipo de objeto!";
			?>		<br>
<?php
			  volta_atras();

		}
		elseif(empty($_POST['estado_objeto']))
		{
			echo "Não selecionou o estado do objeto!";
			?>		<br>
<?php
			volta_atras();

		}
		else
		{
			$query_insere = "INSERT INTO object (name, state, obj_type_id) VALUES ('" . $_POST['nome_objeto'] . "','" . $_POST['estado_objeto'] . "','" . $_POST['tipo_objeto'] . "')";
		if (mysqli_query($ligacao, $query_insere))
		{
			echo "<i>Inseriu os dados do novo objeto com sucesso.</i><br>";
			?>
			<i>Clique em <a href="gestao-de-objetos">Continuar</a> para avançar</i>
<?php
				}
			}
		}
	}

?>
