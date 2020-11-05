<?php
require_once("custom/php/common.php");

if(!is_user_logged_in())
{
   die("Tem que fazer o login");
}
// else
// {
//     echo 'Utilizador tem sessão iniciada'.'<br>';
// }
 if(!current_user_can( "dynamic_search"))
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

 if(!isset($_REQUEST['estado']))
 {
?>
    <h3>Pesquisa Dinâmica - escolher objeto</h3>

<?php

//nome e id do tipo de objeto
$query_tipo_objeto = "SELECT DISTINCT obj_type.id, obj_type.name FROM obj_type, object WHERE obj_type.id = object.obj_type_id";
$result_tipo_objeto = mysqli_query($ligacao, $query_tipo_objeto);

  //Verifica erro na query
  if(!$result_tipo_objeto)
  {
      die ("erro na query:".mysqli_error($ligacao));
  }


  echo "<ul>";
    while($row = mysqli_fetch_assoc($result_tipo_objeto))
    {
      $tipo_obj_nome = $row['name'];
      $id_tipo_objeto = $row['id'];

      echo '<li><label>'.$tipo_obj_nome.'<label></li>';

      //nome e id do objeto referenciado obj_fk_id
      $query_obj_fk = "SELECT DISTINCT object.name, object.id FROM object, attribute WHERE $id_tipo_objeto = object.obj_type_id AND attribute.obj_id = object.id AND attribute.obj_fk_id != object.id";
      $result_obj_fk = mysqli_query($ligacao, $query_obj_fk);

      //Verifica erro na query
      if(!$result_obj_fk)
      {
          die ("erro na query2:".mysqli_error($ligacao));
      }

        echo "<ul>";
        while($row_obj = mysqli_fetch_assoc($result_obj_fk))
        {
            $id_objeto = $row_obj['id'];
            $nome_objeto = $row_obj['name'];

              echo '<li><a href="pesquisa-dinamica?estado=escolha&obj='.$id_objeto.'"> ['.$nome_objeto.'] </a></li>';

        }
        echo "</ul>";

    }
    echo "</ul>";

 } //fecha estado

if($_REQUEST['estado']=='escolha')
{

  $id_obj = $_REQUEST['obj'];

  //seleciona o nome do objeto para colocar no <h3>
  $query_objet_name = "SELECT name FROM object WHERE id =	".$id_obj."";
  $result_objet_name = mysqli_query($ligacao,$query_objet_name);

  $row = mysqli_fetch_assoc($result_objet_name);
  $object_name = $row['name'];

  echo "<h3>Pesquisa Dinâmica - ".$object_name." </h3>";


  //buscar nomes dos atributos associados ao objeto escolhido
  $query_atributos = "SELECT attribute.* FROM attribute WHERE $id_obj = attribute.obj_id";
  $result_atributos = mysqli_query($ligacao, $query_atributos);

    //Verifica erro na query
    if(!$result_atributos)
    {
        die ("erro na query1:".mysqli_error($ligacao));
    }

        echo "<br>";
        echo "<ul>";
        while($row = mysqli_fetch_assoc($result_atributos))
        {
          $obj_fk = $row['obj_fk_id'];
          $nome_atributos = $row['name'];
          echo '<li>';
          echo $nome_atributos;
?>
          <label class = "container2">
          <input type = "checkbox" name = "check">
          <span class="checkmark2"></span>
          </label>

<?php
      echo '</li>';
        }
          echo "</ul>";

          //query que seleciona pelo menos um atributo cujo value_type seja "obj_ref" e obj_fk_id referencie o objeto
          $query_atributos_2 = "SELECT DISTINCT attribute.* FROM attribute, object WHERE attribute.value_type = 'obj_ref' AND $obj_fk = attribute.obj_id";
          $result_atributos_2 = mysqli_query($ligacao, $query_atributos_2);

          echo "<br>";

          $linha = mysqli_num_rows($result_atributos_2);
          if($linha == 0)
          {
            echo "<h3>Pesquisa Dinâmica - obj_ref</h3>";
            echo "Não há atributos com 'value_type' igual a obj_ref<br>";
          }
          else
          {
            echo "<h3>Pesquisa Dinâmica - obj_ref</h3>";
            echo "<ul>";

              while($row = mysqli_fetch_assoc($result_atributos_2))
              {
                $id_object = $row['obj_id'];
                $nome_attr = $row['name'];
                $id_attr = $row['id'];

                //query para buscar nome objeto(obj_ref)para colocar no <h3>
                $query_nome_object = "SELECT name FROM object WHERE id = ".$id_object."";
                $result_nome = mysqli_query($ligacao,$query_nome_object);

                $row_nome = mysqli_fetch_assoc($result_nome);
                $nome_obj = $row_nome['name'];    //nome objeto
                //echo $nome_obj;


                echo '<li>'.$nome_attr.'</li>' ;
    ?>
                <label class = "container2">
                <input type = "checkbox" name = "check2">
                <span class="checkmark2"></span>
                </label>
    <?php

              }
            echo "</ul>";
          }


      echo "<h3>Valores permitidos - ".$nome_attr."</h3>";

      echo "<ul>";

          //buscar value e id da tabela attr_allowed_value
          $query_valores_permitios = "SELECT DISTINCT * FROM attr_allowed_value WHERE $id_attr = attr_allowed_value.attribute_id";
          $result_valores_permitidos = mysqli_query($ligacao,$query_valores_permitios);

          if(!mysqli_num_rows($result_valores_permitidos))
          {
            echo "Não tem valores permitidos associados";
          }
          else
          {

              while($row_value = mysqli_fetch_assoc($result_valores_permitidos))
              {

                $nome_valor_permitido = $row_value['value'];
                echo '<li>'.$nome_valor_permitido.'</li>';
?>
                <label class = "container2">
                <input type = "checkbox" name = "check3">
                <span class="checkmark2"></span>
                </label>
<?php
                //echo $nome_atributo;


              }
                echo "</ul>";
          }

} //fecha estado escolha



if($_REQUEST['estado'] == 'execucao')
{
    //executar dinamicamente uma query

    // $atributo = $_POST['check'];
    // echo $atributo;
  // if(empty($_POST['check']))
  // {
  //   echo "Selecione um atributo!";
  // }

}

?>
