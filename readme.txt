EVENTOS PARA OTROS MÓDULOS (antes de devolver los datos al TPV)
================================================================
Permiten a otros módulos (opcionales) añadir o cambiar lo que se envía al TPV sin tocar este módulo.
En los eventos con "transport" (DataObject), los datos que se devuelven son los de transport:
    $transport = $observer->getEvent()->getData('transport');
    $transport->setData('mi_campo', 'valor');

- gsoft_webpos_before_return_product        catalog/list, por cada producto
      product, extension_attributes, stock (StockInterface: setQty/setStatus), search_criteria
      Datos de venta comunes (en extension_attributes; en getsimples, con las mismas claves en cada hijo):
      webpos_unit (unidad del precio y la cantidad, null = unidades), webpos_min_qty, webpos_max_qty,
      webpos_qty_increments (null = sin incrementos), webpos_stock_editable (false = el TPV no puede cambiar
      el stock) y el stock en webpos_stock. Un módulo que gestione el
      stock de otra forma (ej: Gsoft_AdvancedStock) los sobrescribe en este evento.
      Para añadir campos: declararlos como extension attributes de ProductInterface en el otro módulo
      (etc/extension_attributes.xml) y hacer $extensionAttributes->setData('codigo', valor).
- gsoft_webpos_before_return_product_list   catalog/list, al final: result (ProductSearchResults), search_criteria
- gsoft_webpos_before_return_simple         catalog/getsimples, por cada hijo: transport (array del hijo), product (hijo), parent, store_id
- gsoft_webpos_before_return_simples        catalog/getsimples, al final: transport (resultado), product (configurable), store_id
- gsoft_webpos_before_return_quote_item     quote/create, por cada línea: transport (array de la línea), quote_item, quote
- gsoft_webpos_before_return_quote          quote/create (ya existía): quote (array por referencia)
- gsoft_webpos_before_return_quote_data     quote/create, al final: transport (array del presupuesto), quote
- gsoft_webpos_before_return_order          quote/prepare (crear pedido), al final: transport, order, quote
- gsoft_webpos_before_return_payment        order/pay: transport, payment, order (null si falló al cargarlo)
- gsoft_webpos_before_return_booking        order/book: transport, order_id, status
- gsoft_webpos_before_return_config         config/list: config (objeto ConfigInterface), website_id
- gsoft_webpos_before_stock_reservation_sources  al repartir cada línea de un pedido web entre almacenes:
      transport (stocks: código => disponible; default_source: almacén del TPV), order_item, order
- gsoft_webpos_before_save_stock_reservation  al guardar cada reserva de webpos_stock_reservation (pedido web):
      reservation (modelo Stockreservation, sin guardar), order_item, order. Ej: setData('unit', 'm')
- gsoft_webpos_before_returncustomer_name   quote/create (ya existía): customer_name (por referencia), quote, customer
