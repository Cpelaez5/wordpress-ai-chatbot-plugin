<?php // HACER QUERYS A LA BASE DE DATOS
    global $wpdb;

    $tabla = "{$wpdb->prefix}encuestas";
    $tabla2 = "{$wpdb->prefix}encuestas_detalle";

    if (isset($_POST['btn-guardar'])){
        $nombre = $_POST['txtnombre'];
        $query = "SELECT EncuestaID FROM $tabla ORDER BY EncuestaID DESC LIMIT 1";
        $resultado = $wpdb->get_results($query,ARRAY_A);
        $proximoID = $resultado[0]['EncuestaID']+1;
        $shortcode = "[ENC_0".$proximoID."]";
        $datos = [
            'EncuestaID' => null,
            'Nombre' => $nombre,
            'ShortCode' => $shortcode
        ];

        $respuesta = $wpdb->insert($tabla,$datos);

        if($respuesta){
            $listaPreguntas = $_POST['name'];
            $i = 0;

            foreach ($listaPreguntas as $key => $value) {
                $tipo = $_POST['type'][$i];
                
                $datos2 = [
                    'DetalleID' => null,
                    'EncuestaID' => $proximoID,
                    'Pregunta' => $value,
                    'Tipo' => $tipo
                ];
                $wpdb->insert($tabla2,$datos2);
                $i++;
            }
        }

        
       
    }

    $query= "SELECT * FROM $tabla";

    $listaEncuestas = $wpdb->get_results($query,ARRAY_A);

    if(empty($listaEncuestas)){
        $listaEncuestas = array();
    }
?>

<div class="wrap">
    
    <?php 
    echo "<h1 class='wp-heading-inline'>" . get_admin_page_title() . "</h1>";
    ?>
    <a id="btn-add-new" class="page-title-action">Añadir nuevo</a>
    <br><br><br>


    
<table  class="wp-list-table widefat fixed striped pages">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Nombre de encuesta</th>
      <th scope="col">Shortcode</th>
      <th scope="col">Acciones</th>
    </tr>
  </thead>
  <tbody id="the-list">

   <?php

        foreach ($listaEncuestas as $key => $value){

            $ID = $value['EncuestaID'];
            $nombre = $value['Nombre'];
            $shortcode = $value['ShortCode'];

            echo "
                <tr>
                    <th scope='row'>{$ID}</th>
                    <td>{$nombre}</td>
                    <td>{$shortcode}</td>
                    <td>
                        <a class='page-title-action'>Ver estadisticas</a>
                        <a class='page-title-action'>Borrar</a>
                    </td>
                </tr>
                ";
        }
    ?>
  </tbody>
</table>

</div>

<!-- modal -->

<div class="modal fade" id="bootstrapModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Añadir nueva encuesta</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="post"> 

      <div class="modal-body">
    
            <div class="form-group">
                <label for="txtnombre" class="form-label">Nombre de la encuesta</label>
                <div class="col-sm-8">
                <input type="text" class="form-control" id="txtnombre" name="txtnombre" style="width:100%;">
                </div>
            </div> 
            <br>
            <hr>
            <h4>Preguntas</h4>
            <hr>
            <br>
            <table id="camposDinamicos">
                <tr>
                    <td><label for="txtnombre" class="form-label" style="margin-right:5px;">Pregunta 1</label></td>
                    <td><input type="text" class="form-control name_list" id="name" name="name[]" style="width:100%;"></td>
                    <td><select name="type[]" id="type" class="form-control type_list" style="margin-left:5px;">
                        <option value="1">Sí/No</option>
                        <option value="2">Rango 0-5</option>
                    </select></td>
                    <td><button name="add" id="add" type="button" class="btn btn-success" style="margin-left:5px;">Agregar más</button></td>
                </tr>
            </table>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" name="btn-guardar" id="btn-guardar" class="btn btn-primary">Guardar</button>
      </div>

      </form> 

    </div>
  </div>
</div>


