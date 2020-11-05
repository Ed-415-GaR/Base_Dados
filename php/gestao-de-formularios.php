<?php
require_once("custom/php/common.php");

if(!is_user_logged_in())
{
   die("Tem que fazer o login");
}else
// {
//     echo 'Utilizador tem sessão iniciada'.'<br>';
// }
 if(!current_user_can( "manage_custom_forms"))
 {
     die("Não tem autorização para aceder a esta página");
 }
 // else
 // {
 //     echo 'Autorização concedida'.'<br>';
 // }

 //Ligação BD
  $ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

  if(!$ligacao)
  {
      echo '<strong>Erro ao ligar à Base de dados</strong>'.mysqli_error($ligacao);
  }
  // else
  // {
  //     echo 'Ligação realizada com sucesso'.'<br>';
  // }

	$estado = isset($_REQUEST["estado"]);
	if (!$estado)
	{

		?>
		<h3>Gestão de Formulários Customizados</h3>
		<?php

		//Busca de formularios costumizados
		$query_custom_form = "SELECT custom_form.id,
		custom_form.name
		FROM custom_form, custom_form_has_attribute
		WHERE custom_form.id = custom_form_has_attribute.custom_form_id";
		$result_custom_form =  mysqli_query($ligacao,$query_custom_form);

		if(!$result_custom_form) {
			die("Ocorreu um erro ao executar a query 1 ".mysqli_error($ligacao));
		}

 		//if (!mysqli_num_rows($result_custom_form > 0) {

 		?>
 		<table class = "mytable" border width = "100%">
	<thead>
		<tbody>
		<tr>
 				<th>Formulário customizado</th>
				<th>Id</th>
				<th>Nome do atributo</th>
 				<th>Tipo de valor</th>
				<th>Nome do campo no formulário</th>
 				<th>Tipo do campo no formulário</th>
 				<th>Tipo de unidades</th>
 				<th>Ordem do campo no formulário</th>
				<th>Tamanho do campo no formulário</th>
 				<th>Obrigatório</th>
 				<th>Estado</th>
				<th>Ação</th>
			</tr>
 		</thead>


 <?php
			//}

			//Busca de formulario que tem atributos
			$query_form_attrib = "SELECT DISTINCT custom_form.name,
			custom_form_has_attribute.custom_form_id as id
			FROM custom_form_has_attribute, custom_form
			WHERE custom_form.id = custom_form_has_attribute.custom_form_id";
			$result_form_attr = mysqli_query($ligacao, $query_form_attrib);
			if (!$result_form_attr) {
				die("Ocorreu um erro ao executar a query 2 ".mysqli_error($ligacao));
			}

			while ($row = mysqli_fetch_assoc($result_form_attr))
			{
				$form_id = $row['id'];

				//Busca de Atributos para cada formulario
				$query_attributes = "SELECT DISTINCT attribute.*
				FROM attribute, custom_form, custom_form_has_attribute
				WHERE custom_form_has_attribute.custom_form_id = '$form_id'  AND custom_form_has_attribute.attribute_id = attribute.id";
				$result_attributes = mysqli_query($ligacao, $query_attributes);
				if(!$result_attributes) {
					die("Ocorreu um erro ao executar a query 3 ".mysqli_error($ligacao));
				}

				$linhas = mysqli_num_rows($result_attributes);
				echo '<tr>';
				echo "<td rowspan='".$linhas."'> <a href=?data=".$row['id']."&estado=editar_form>".$row['name']."</td>"; // Calcula quantas Celulas irá ocupar na tabela / método GET

				$beginrow = true;

				while ($row = mysqli_fetch_assoc($result_attributes)){
					$id_attr = $row['id'];
					$id_unit_type = $row['unit_type_id'];
					$obrigatorio = $row["mandatory"];
					//Busca ordena do formulario
					$query_form_custom_has_attribute = "SELECT DISTINCT *
					FROM custom_form_has_attribute
					WHERE custom_form_id = '$form_id' AND  attribute_id = '$id_attr' ORDER BY custom_form_has_attribute.field_order ASC";
					$result_form_costum_has_attribute = mysqli_query($ligacao, $query_form_custom_has_attribute);
					if(!$result_form_costum_has_attribute) {
						die("Ocorreu um erro ao executar a query 4 ".mysqli_error($ligacao));
					}

					$query_unidades = "SELECT * FROM attr_unit_type WHERE attr_unit_type.id = $id_unit_type";
                    $result_unidades = mysqli_query($ligacao, $query_unidades);

					$row_unidades = mysqli_fetch_assoc($result_unidades);

					$name_unidades = $row_unidades['name'];

					if($obrigatorio == 1) {
						$obrigatorio = "sim";
                    }
                    else {
						$obrigatorio = "não";
					}

					if(!$beginrow) {
					?>
					<tr>
						<td><?=$row["id"]?></td>
						<td><?=$row["name"]?></td>
						<td><?=$row["value_type"]?></td>
						<td><?=$row["form_field_name"]?></td>
						<td><?=$row["form_field_type"]?></td>
						<td><?=$name_unidades?></td>
						<td><?=$row["form_field_order"]?></td>
						<td><?=$row["form_field_size"]?></td>
						<td><?=$obrigatorio?></td>
						<td><?=$row["state"]?></td>
						<td>[editar][desativar]</td>
					</tr>
					<?php
					}else {
					?>
						<td><?=$row["id"]?></td>
						<td><?=$row["name"]?></td>
						<td><?=$row["value_type"]?></td>
						<td><?=$row["form_field_name"]?></td>
						<td><?=$row["form_field_type"]?></td>
						<td><?=$name_unidades?></td>
						<td><?=$row["form_field_order"]?></td>
						<td><?=$row["form_field_size"]?></td>
						<td><?=$obrigatorio?></td>
						<td><?=$row["state"]?></td>
						<td>[editar][desativar]</td>
					<?php
					}
					$beginrow = false;
				}
				echo '</tr>';
			}
			echo '</tbody>';
		echo '</table>';

?>
		<br>
		<!-- Criação de Formularios Customizados (Parte 2) ----->
			<h3>Criar formulário Customizado</h3>
			<form method="post">
				<input type="hidden" name="estado" value="inserir">
				<strong> Nome do Formulário</strong><br>
				<input type="text" name="form_nome"> <br>


				<?php
				$attributes_query = "SELECT * FROM attribute ORDER by attribute.id";
				$attributes_result = mysqli_query($ligacao, $attributes_query);
				if(!$attributes_result) {
					die("Ocorreu um erro ao executar a query 5 ".mysqli_error($ligacao));
				}
				if (mysqli_num_rows($attributes_result) > 0) {
				?>
					<table class = "mytable" border width = "100%">
							<thead>
								<tr>
									<th>Objeto</th>
									<th>Id</th>
									<th>Nome do atributo</th>
									<th>Tipo de valor</th>
									<th>Nome do campo no formulário</th>
									<th>Tipo do campo no formulário</th>
									<th>Tipo de unidades</th>
									<th>Ordem do campo no formulário</th>
									<th>Tamanho do campo no formulário</th>
									<th>Obrigatório</th>
									<th>Escolher</th>
									<th>Ordem</th>
								</tr>
							</thead>
						<tbody>
				<?php
				}

				//Busca de objetos que tem atributos
				$object_query = "SELECT DISTINCT object.id as obj_id,
				object.name
				FROM object, attribute
				WHERE attribute.obj_id = object.id";
				$objects_result = mysqli_query($ligacao, $object_query);
				if (!$objects_result) {
					die("Ocorreu um erro ao executar a query 6 ".mysqli_error($ligacao));
				}

				while($row = mysqli_fetch_assoc($objects_result))
				{
				$objects_id = $row['obj_id'];
				$nome_objeto = $row['name'];

					// Busca dos atributos que tem referencia a cada objecto
					$query_attributes_obj = "SELECT DISTINCT * FROM attribute WHERE attribute.obj_id = '$objects_id'";
					$result_attributes_obj = mysqli_query($ligacao, $query_attributes_obj);

					if (!$result_attributes_obj) {
						die("Ocorreu um erro ao executar a query 7 ".mysqli_error($ligacao));
					}


					$linhas = mysqli_num_rows($result_attributes_obj);
								echo "<tr>";

								echo '<td rowspan="'.$linhas.'">'.$nome_objeto.'</td>';
					$beginrow = true;

					while ($row = mysqli_fetch_assoc($result_attributes_obj)){
						if(!$beginrow) {
							?><tr>
									<td><?=$row["id"]?></td>
									<td><?=$row["name"]?></td>
									<td><?=$row["value_type"]?></td>
									<td><?=$row["form_field_name"]?></td>
									<td><?=$row["form_field_type"]?></td>
									<td><?=$row["unit_type_id"]?></td>
									<td><?=$row["form_field_order"]?></td>
									<td><?=$row["form_field_size"]?></td>
									<td><?=$row["mandatory"]?></td>
									<td><input type = 'checkbox' name = 'escolher[]' value = '<?=$row["id"]?>'></td>
									<td><input type = 'text' name = 'ordem[]'></td>
								</tr>
								<?php
						}else{
							?>
									<td><?=$row["id"]?></td>
									<td><?=$row["name"]?></td>
									<td><?=$row["value_type"]?></td>
									<td><?=$row["form_field_name"]?></td>
									<td><?=$row["form_field_type"]?></td>
									<td><?=$row["unit_type_id"]?></td>
									<td><?=$row["form_field_order"]?></td>
									<td><?=$row["form_field_size"]?></td>
									<td><?=$row["mandatory"]?></td>
									<td><input type = 'checkbox' name = 'escolher[]' value = '<?=$row["id"]?>'></td>
									<td><input type = 'text' name = 'ordem[]'></td>
								</tr>
								<?php
						}
						$beginrow = false;
					}
					echo '</tr>';
					?>
					<?php

			}
			?>
		</tbody>
</table>
<br><input type='Submit' value='Inserir formulário'><br>
</form>

			<?php

} // Submeter dados, fecho do If responsável pelo Request (!estado)


//-------------------Tratamento de Inserção de dados Submetidos (Parte 3) -------------------
	if($_REQUEST['estado'] == 'inserir') {
		$nome_form = $_REQUEST['form_nome'];

		foreach ($_REQUEST['ordem'] as $dados_ordem) { //Erro de integridade de dados no campo ordem
			if ($dados_ordem > 0) {
				$ordem_listagem[] = $dados_ordem;
				if(!is_numeric($dados_ordem)) {
					echo "Insira um valor numérico no campo Ordem <br>";
					voltar_atras();
				}
			}
		}

		$len_escolher = count($_REQUEST["escolher"]);
		$len_ordem = count($ordem_listagem);

		if ($len_escolher != $len_ordem) {
			echo "Numero de ordem é diferente do escolher<br>";
			volta_atras();
			exit();
		}

		if (empty($nome_form)) {
			echo "<strong>ATENÇÃO:</strong> Nome do formulario é obrigatório<br>";
			volta_atras();
			exit();
		}
		$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

		$query_forms_custom ="SELECT name FROM custom_form";
		$result_forms_custom =  mysqli_query($ligacao, $query_forms_custom);

		if (!$result_forms_custom) {
			die("Ocorreu um erro ao executar a query 8 ".mysqli_error($ligacao));
		}

		if (!empty($nome_form)) {
			$dupe = false;
			while (($row = mysqli_fetch_assoc($result_forms_custom)) && $dupe == false) {
				if ($row['name'] == $nome_form) {
					$dupe = true;
					echo "<strong>ATENÇÃO:</strong>Já existe um formulário com o mesmo nome";
					echo "<br>";
					volta_atras();
					exit();
				}
			}
		}
		else {
			echo "O nome do formulario está vazio";
			volta_atras();
			exit();
		}

		if ($len_ordem == $len_escolher) {


			$query_insert = "INSERT INTO custom_form (name) VALUES ('$nome_form')";
			$result_insert = mysqli_query($ligacao, $query_insert);

			if(!$result_insert) {
				die ("Ocorreu um erro ao inserir o formulario 1 ". mysqli_error($ligacao));
			}

			$ult_id = mysqli_insert_id($ligacao); //ID da ultima insercao na DB_HOST

			foreach (array_combine($_REQUEST['escolher'], $ordem_listagem) as $key => $value){ // array de chaves e valores {[k,v]...}
				$insert_query = "INSERT INTO custom_form_has_attribute (custom_form_id, attribute_id, field_order) VALUES ('$ult_id', '$key', $value)";
				$result_insert_query = mysqli_query($ligacao, $insert_query);

				//if (!$result_insert_query) {
					//die ("Ocorreu um erro ao inserir o formulario 2 ". mysqli_error($ligacao));
				//}

			}
		if ($result_insert && $result_insert_query) {
					echo "Dados inseridos na Base de Dados com sucesso <br>";
					echo "Clique em  <a href='gestao-de-formularios'> Continuar </a> para avançar <br>";
				}

		}


} // Inserção de dados, Fecho do If responsável pelo Request == 'estado'


//------------------------ Edição de Formulários (Parte 4) ------------------------
	if ($_REQUEST['estado'] == 'editar_form') {

		$url_id = $_GET['data'];
		$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

		//Busca do formulario criado que é pretendido editar
		$query_edit = "SELECT name FROM custom_form WHERE '$url_id' = custom_form.id";
		$result_edit = mysqli_query($ligacao, $query_edit);

		if (!$result_edit) {
			die("Ocorreu um erro ao executar a query 9 ".mysqli_error($ligacao));
		}
?>

		<h3>Editar Formulario Customizado</h3>
		<form method= "POST">
			<br> Nome do Formulário <br>
			<?php
			$row = mysqli_fetch_assoc($result_edit);
			?>
			<input type= "text" name= 'nome_form' value= '<?=$row["name"]?>'>
<?php

		$query_atributos = "SELECT * FROM attribute";
		$result_atributos = mysqli_query($ligacao, $query_atributos);

		if (!$result_atributos) {
			die("Ocorreu um erro ao executar a query 10 ".mysqli_error($ligacao));
		}

		if (mysqli_num_rows($result_atributos) > 0) {
		?>
			<table class = "mytable" border="100%">
			<thead>
				<tbody>
				<tr>
					<!-- <th>Nome do Formulario</th> -->
					<th>Id</th>
					<th>Nome do atributo</th>
					<th>Tipo de valor</th>
					<th>Nome do campo no formulário</th>
					<th>Tipo do campo no formulário</th>
					<th>Tipo de unidades</th>
					<th>Ordem do campo no formulário</th>
					<th>Tamanho do campo no formulário</th>
					<th>Obrigatório</th>
					<th>Escolher</th>
					<th>Ordem</th>
				</tr>
			</thead>
			<?php
		}

		//Busca dos atributos dos formulários inseridos.
		$query_atributos_form = "SELECT DISTINCT attribute.* FROM attribute, custom_form, custom_form_has_attribute
		WHERE attribute.id = custom_form_has_attribute.attribute_id AND '$url_id' = custom_form_has_attribute.custom_form_id";
		$result_atributos_form = mysqli_query($ligacao, $query_atributos_form);

		if(!$result_atributos_form) {
			die("Ocorreu um erro ao executar a query 11 ".mysqli_error($ligacao));
		}

		while ($row = mysqli_fetch_assoc($result_atributos_form)) {
			$atributos_id = $row["id"];
			$query_ordem = "SELECT * FROM custom_form_has_attribute
			WHERE custom_form_has_attribute.attribute_id = '$atributos_id' AND custom_form_has_attribute.custom_form_id = '$url_id'";
			$result_ordem = mysqli_query($ligacao, $query_ordem);

			if (!$result_ordem) {
				die("Ocorreu um erro ao executar a query 12 ".mysqli_error($ligacao));
			}

			$ordem_row = mysqli_fetch_assoc($result_ordem);
		?>
				<tr>
					<td><?=$row["id"]?></td>
					<td><?=$row["name"]?></td>
					<td><?=$row["value_type"]?></td>
					<td><?=$row["form_field_name"]?></td>
					<td><?=$row["form_field_type"]?></td>
					<td><?=$row["unit_type_id"]?></td>
					<td><?=$row["form_field_order"]?></td>
					<td><?=$row["form_field_size"]?></td>
					<td><?=$row["mandatory"]?></td>
					<td><input type = 'checkbox' name = 'escolher[exists][]' value = '<?=$row["id"]?>' checked></td>
					<td><input type = 'text' name = 'ordem[exists][]' value = '<?=$ordem_row["field_order"]?>'></td>
				</tr>


		<?php
		}

		//Busca dos atributos nao selecionados no inserido.
		$query_NotAttribute = "SELECT * FROM attribute WHERE attribute.id NOT IN
		(SELECT attribute_id FROM custom_form_has_attribute WHERE custom_form_has_attribute.custom_form_id ='$url_id')"; //id attrib inserido
		$result_NotAttribute = mysqli_query($ligacao,$query_NotAttribute);

		if (!$result_NotAttribute) {
			die("Ocorreu um erro ao executar a query 13 ".mysqli_error($ligacao));
		}

		while ($row = mysqli_fetch_assoc($result_NotAttribute)) {
		?>
				<tr>
					<td><?=$row["id"]?></td>
					<td><?=$row["name"]?></td>
					<td><?=$row["value_type"]?></td>
					<td><?=$row["form_field_name"]?></td>
					<td><?=$row["form_field_type"]?></td>
					<td><?=$row["unit_type_id"]?></td>
					<td><?=$row["form_field_order"]?></td>
					<td><?=$row["form_field_size"]?></td>
					<td><?=$row["mandatory"]?></td>
					<td><input type = 'checkbox' name = 'escolher[not_exists][]' value = '<?=$row["id"]?>'></td>
					<td><input type = 'text' name = 'ordem[not_exists][]'></td>
				</tr>
				<?php
		}
		?>
			</tbody>
			</table>
		<input type = 'hidden' name = 'estado' value = 'atualizar_form_custom'>
		<br>
		<input type = 'submit' name = 'submit' value = 'Confirmar Edição'>
	</form>


<?php

}	//Editar Formularios, Fecho do IF Responsável pelo Request == 'editar_form' (derivado da referencia por Metodo GET em caso de 'EDIT')

	//------------------------ Atualização de Formulários (Parte 5) ------------------------
if ($_REQUEST['estado'] == 'atualizar_form_custom') {

	$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

	$url_id = $_GET['data'];
	$nome_form = $_REQUEST['nome_form'];
	$deletes = false;
	$query_attributes_form = "SELECT * FROM custom_form_has_attribute WHERE custom_form_has_attribute.custom_form_id = '$url_id'";
	$result_attributes_form = mysqli_query($ligacao, $query_attributes_form);

if (!$result_attributes_form) {
	die("Ocorreu um erro ao executar a query 13 ".mysqli_error($ligacao));
}

while ($row = mysqli_fetch_assoc($result_attributes_form)) {
	$id_atributos[] = $row['attribute_id'];
}

$id_check = array_diff($id_atributos, $_POST['escolher']['exists']);

if($id_check == 0 && empty($_POST['escolher']['exists'])) {
	$query_apaga_form = "DELETE FROM custom_form_has_attribute WHERE custom_form_has_attribute.custom_form_id = '$url_id'";
	$result_apaga_form = mysqli_query($ligacao, $query_apaga_form);

	if (!$result_apaga_form) {
		die ("Ocorreu um erro ao Apagar o formulario 1 ". mysqli_error($ligacao));
	}
	// Caso forem des-selecionados todos os atributos
	$query_apaga = "DELETE FROM custom_form WHERE custom_form.id = '$url_id'";
	$result_apaga = mysqli_query($ligacao, $query_apaga);

	if (!$result_apaga) {
		die ("Ocorreu um erro ao Apagar o formulario 2 ". mysqli_error($ligacao));
	}
	else {
		echo "O Formulário foi apagado com sucesso. <br>";
		echo "Clique em  <a href='gestao-de-formularios'> Continuar </a> para avançar. <br>";
		$deletes = true;
	}
}

// Selecionados novos atributos, para ser Inseridos

if (isset($_POST['escolher']['not_exists'])) {
		foreach ($_POST['ordem']['not_exists'] as $key) {
		if ($key != null) {
			$dados_ordem[] = $key;
		}
	}

	$num_ordem = count($dados_ordem);
	$num_escolher = count($_POST['escolher']['not_exists']);

	if ($num_ordem != $num_escolher) {
		echo "Falta Inserir um valor de Ordem ou Escolher, por favor inserir ambos! <br>";
	}

	if ($num_ordem == $num_escolher) {
		$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$par_array = array_combine($_POST['escolher']['not_exists'], $dados_ordem);
		foreach ($par_array as $valor => $chave) {
			$query_insert_update = "INSERT INTO custom_form_has_attribute (custom_form_id, attribute_id, field_order)
				VALUES ('$url_id','$valor','$chave')";
			$result_insert_update = mysqli_query($ligacao, $query_insert_update);
		}
	}

	if(!$result_insert_update) {
		//die ("Ocorreu um erro ao inserir o formulario 3 ". mysqli_error($ligacao));
		echo "<br> <Strong>(Problema conhecido):</Strong> Por favor volte a criar o último Formulário que tentou criar caso este não apareça na lista. <br>";
	}
	else {
		echo "Os novos atributos do Formulário foram inseridos com sucesso. <br>";
		echo "Clique em  <a href='gestao-de-formularios'> Continuar </a> para avançar. <br>";
	}
}

 if (!empty ($id_atributos)) {
	if ($id_check != 0) {
		foreach ($id_check as $key => $value) {
			$query_apaga_2 = "DELETE FROM custom_form_has_attribute
			WHERE custom_form_has_attribute.custom_form_id = '$url_id' AND custom_form_has_attribute.attribute_id = $value";
			//apaga os atributos des-selecionados na edição
			$result_apaga_2 = mysqli_query($ligacao, $query_apaga_2);
		}
		if (!$result_apaga_2) {
			echo "<br>";
		}
		else {
			echo "Os atributos do Formulário foram apagados com sucesso. <br>";
			echo "Clique em  <a href='gestao-de-formularios'> Continuar </a> para avançar. <br>";
			$deletes = true;
		}
	}
}

$muda_nome = false;
if ($dupe == false) { //se nao tiver duplicado
	$query_update = "UPDATE custom_form SET name = '$nome_form' WHERE id = '$url_id'";
	$result_update = mysqli_query($ligacao, $query_update);

	if(!$result_update) {
		die ("Ocorreu um erro ao fazer UPDATE ao formulario 1 ". mysqli_error($ligacao));
	}
	else {
		echo "O Formulário foi gerido com sucesso. <br>";
		echo "Clique em  <a href='gestao-de-formularios'> Continuar </a> para avançar. <br>";
		$muda_nome = true;
	}
}
} // Actualizar Formulários, Fecho do IF Responsável pelo Request == 'atualizar_form_custom' (derivado do Submit de Edições a um Formulário)


 ?>
