var updatePanierUrl = '{{ path("update_panier") }}';
//function to update qte



function updateQuantity(index, change, code = null, type = 'quantity') {
    let selector, minValue;

    switch (type) {
        case 'quantity':
            selector = `.quantity-input[data-index="${index}"]`;
            minValue = 0;
            break;
        case 'quantityPromo':
            selector = `.quantity-input-promo[data-index="${index}"]`;
            minValue = 0;
            break;
        case 'quantityGift':
            selector = `.quantity-input-gift[data-index="${index}"]`;
            minValue = 0;
            break;
        default:
            console.error(`Type de quantité non reconnu: ${type}`);
            return;
    }

    const tbody = type === 'quantityGift' 
        ? document.querySelector('#articles-gift-list') 
        : document.querySelector('tbody');

    const quantityInput = tbody.querySelector(selector);

    if (!quantityInput) {
        console.error(`L'élément input avec data-index="${index}" n'existe pas pour le type ${type}.`);
        return;
    }

    let quantity = parseInt(quantityInput.value) + change;
    if (quantity < minValue) quantity = minValue;
    quantityInput.value = quantity;

    if (code != null && type == "quantityGift" ) {
        updateQtePanier(code, quantity, type , true );
    }
    if (code != null ) {
        updateQtePanier(code, quantity, type , false);
    }
}

function updateQtePanier(code, quantity, quantityType  , isGift) {

    const validQuantityTypes = ['quantity', 'quantityPromo', 'quantityGift'];
    if (!validQuantityTypes.includes(quantityType)) {
        console.error('Type de quantité invalide');
        return;
    }
    const data = {
        code: code,
        [quantityType]: isNaN(quantity) ? 0 : quantity , 
        isGift : isGift
    };
    $.ajax({
        url: updatePanierUrl,
        type: 'POST',
        data: data,
        success: function (response) {
            if (response.status === 'success' && response.articleExists) {
                showAlert();
            }
            console.log("Panier mis à jour avec succès:", response);
        },
        error: function (error) {
            console.error("Erreur lors de la mise à jour du panier:", error);
        }
    });
}

let alertTimeout = null;

function showAlert() {
    if (alertTimeout) {
        clearTimeout(alertTimeout);
    }
    alertTimeout = setTimeout(function () {
        Swal.fire({
            title: 'Quantité mise à jour',
            text: `La quantité de l'article a été mise à jour.`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
        alertTimeout = null;
    }, 1000);
}
