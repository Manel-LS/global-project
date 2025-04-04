

$(document).ready(function () {
    var clientsData = document.getElementById('clients-data').getAttribute('data-clients');
    var clients = JSON.parse(clientsData);


    var isUpdating = false;

    $('#codetrs').change(function () {
       if (!isUpdating) {
          isUpdating = true;
          $('#libtrs').val(clients[$(this).val()]).trigger('change');
          isUpdating = false;
       }
    });

    $('#libtrs').change(function () {
       if (!isUpdating) {
          isUpdating = true;
          var selectedName = $(this).val();
          for (var code in clients) {
             if (clients[code] === selectedName) {
                $('#codetrs').val(code).trigger('change');
                break;
             }
          }
          isUpdating = false;
       }
    });

    $('#libtrs').on('select2:selecting', function (e) {
       $(this).select2('close');
    });

    $('#codetrs').on('select2:selecting', function (e) {
       $(this).select2('close');
    });
 });
function initializeSelect2() {
    $('#libtrs, #codetrs').select2({
        placeholder: "Choisir un client"
    });


    var isUpdating = false;

    $('#codetrs').change(function () {
        if (!isUpdating) {
            isUpdating = true;
            $('#libtrs').val(clients[$(this).val()]).trigger('change');
            isUpdating = false;
        }
    });

    $('#libtrs').change(function () {
        if (!isUpdating) {
            isUpdating = true;
            var selectedName = $(this).val();
            for (var code in clients) {
                if (clients[code] === selectedName) {
                    $('#codetrs').val(code).trigger('change');
                    break;
                }
            }
            isUpdating = false;
        }
    });

    $('#libtrs, #codetrs').on('select2:selecting', function (e) {
        $(this).select2('close');
    });
}

function showAlert(icon, title, text) {
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        confirmButtonText: 'OK',
    });
}
function showConfirmationAlert(title, text, confirmButtonText, cancelButtonText) {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText
    });
}

function logError(message, error) {
    console.error(message, error);
}