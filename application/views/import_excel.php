<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Importar xlxs / xlx</title>
        <style>
            form {
                /* Centrar el formulario en la página */
                margin: 0 auto;
                width: 40%;
                /* Esquema del formulario */
                padding: 1em;
                border: 2px solid #CCC;
                border-radius: 1em;
            }
            #title{
                margin: 0 auto;
            }
            #btnImport{
                margin-top: 5px;
            }
            #divError{
                margin-top: 5px;
                color: #FF0000;     
            }
            #divSuccess{
                margin-top: 5px;
                color: #008000;     
            }
            
        </style>
            <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
            
    </head>
    <body>
        <form id='formUpload' action="<?php echo base_url()?>index.php/import_excel/import" method="POST" enctype="multipart/form-data"> 
            <h4 id="title">Importar archivos</h4>
            <hr>
            <div class="form-group">
                <table>
                    <tr>
                        <td>
                            <label for="userfile">Seleccione archivo</label>
                            <input id="userfile" name="userfile"  class="form-control" type="file">
                        </td>                      
                    </tr>
                    <tr>
                        <td>
                        <label>¿Es SAC?<input type="checkbox" name="checkSac" id="checkSac" value="1"></label><br>
                        </td>                      
                    </tr>
                    <tr>
                        <td>
                            <button id="btnImport" name="btnImport" type="submit">Importar</button>
                        </td>
                    </tr>
                </table>
            </div>
            <div id="divError"><?php echo $error;?></div>
            <div id="divSuccess"><?php echo $success;?></div>

        </form>

    </body>
</html>

