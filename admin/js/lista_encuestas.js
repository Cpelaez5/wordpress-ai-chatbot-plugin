jQuery(document).ready(function($){


    $('#btn-add-new').click(function(){
        $('#bootstrapModal').modal('show');
    })
    
    var i = 1;

    $('#add').click(function(){
        i++;
        $('#camposDinamicos').append
        $('#camposDinamicos').append(`
            <tr id="row${i}">
                    <td><label for="txtnombre" class="form-label" style="margin-right:5px;">Pregunta ${i}</label></td>
                    <td><input type="text" class="form-control name_list" id="name" name="name[]" style="width:100%;"></td>
                    <td><select name="type[]" id="type" class="form-control type_list" style="margin-left:5px;">
                        <option value="1">Sí/No</option>
                        <option value="2">Rango 0-5</option>
                    </select></td>
                    <td><button name="remove" id="${i}" type="button" class="btn btn-danger btn_remove" style="margin-left:5px;">X</button></td>
                </tr>
            `);
        return false;
    });

    $(document).on('click','.btn_remove',function () {
        var buttonId = $(this).attr('id');
        $(`#row${buttonId}`).remove();
    });


})