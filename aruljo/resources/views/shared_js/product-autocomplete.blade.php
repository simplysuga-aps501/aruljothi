<script>
/* ---------------- PRODUCT PILL HANDLING ---------------- */
function initProductPills(containerSelector, productsList) {
    $(containerSelector).each(function () {
        let container = $(this);
        let pillsContainer = container.find(".product-pills");
        let textarea = container.find(".product-detail");
        let searchInput = container.find(".product-search");
        let qtyInput = container.find(".product-qty");
        let addBtn = container.find(".product-add");
        let alertBox = container.find(".product-alert");

        // ---------------- ADD NEW PILL ----------------
        addBtn.off("click").on("click", function () {
            var name = searchInput.val().trim();
            var qty = qtyInput.val().trim();

            if (!name) return showProductError(alertBox, "Enter a product name.");
            if (!qty || qty <= 0) return showProductError(alertBox, "Quantity is required.");
            if (productsList && !productsList.some(p => p.name === name)) {
                return showProductError(alertBox, "Select a valid product from the list.");
            }

            // Prevent duplicates
            var exists = false;
            pillsContainer.find(".badge").each(function () {
                var text = $(this).clone().children().remove().end().text().trim();
                if (text.split(" , ")[0].toLowerCase() === name.toLowerCase()) {
                    exists = true;
                    return false;
                }
            });
            if (exists) return showProductError(alertBox, "This product already added.");

            // Find from list
            var productObj = productsList.find(p => p.name === name);
            var weight = productObj ? parseFloat(productObj.weight) : 0;
            var sku = productObj ? productObj.sku : null;
            var id = productObj ? productObj.id : null;
            var price = productObj ? parseFloat(productObj.price) : 0;

            // Create pill
            var pill = $('<span class="pill badge badge-info mr-1 mb-1">' + name + ' , ' + qty +
                ' <i class="fas fa-times ml-1" style="cursor:pointer;"></i></span>');

            pill.data({ name, id, sku, qty, weight, price });

            // Delete icon
            pill.find('i').on('click', function (e) {
                e.stopPropagation();
                pill.remove();
                updateProductTextarea(pillsContainer, textarea);
            });

            pillsContainer.append(pill);
            updateProductTextarea(pillsContainer, textarea);

            searchInput.val('');
            qtyInput.val('');
        });

        // ---------------- REBUILD EXISTING PILLS ----------------
        var existing = textarea.val();
        if (existing) {
            var items = existing.split('~|~');
            items.forEach(function (item) {
                var parts = item.split(",");
                var name = parts[0].trim();
                var qty = (parts[1] || "").trim();

                var productObj = productsList.find(p => p.name === name);
                var weight = productObj ? parseFloat(productObj.weight) : 0;
                var sku = productObj ? productObj.sku : null;
                var id = productObj ? productObj.id : null;
                var price = productObj ? parseFloat(productObj.price) : 0;

                var pill = $('<span class="pill badge badge-info mr-1 mb-1">' + name + ' , ' + qty +
                    ' <i class="product-pill-remove fas fa-times ml-1" style="cursor:pointer;"></i></span>');

                pill.data({ name, id, sku, qty, weight, price });

                pill.find('i').on('click', function () {
                    pill.remove();
                    updateProductTextarea(pillsContainer, textarea);
                });

                pillsContainer.append(pill);
            });
        }

        // ---------------- RE-BIND PILL CLICK (EDIT MODE) ----------------
        // ✅ Use delegated event binding so it works for newly added pills too
        pillsContainer.off('click', '.pill').on('click', '.pill', function (e) {
            if ($(e.target).is('i')) return; // ignore delete icon
            const pill = $(this);
            searchInput.val(pill.data('name'));
            qtyInput.val(pill.data('qty'));
            pill.remove();
            updateProductTextarea(pillsContainer, textarea);
        });

    });
}

function updateProductTextarea(pillsContainer, textarea) {
    var details = [];
    pillsContainer.find(".badge").each(function () {
        var text = $(this).clone().children().remove().end().text().trim();
        details.push(text);
    });
    textarea.val(details.join('~|~'));
}

function showProductError(alertBox, msg) {
    var alert = $(alertBox);
    alert.text(msg).removeClass('d-none');
    setTimeout(() => alert.addClass('d-none'), 3000);
}
</script>
