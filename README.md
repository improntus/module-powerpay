# Module Powerpay
## Descripción
Powerpay para Magento (Adobe Commerce) te ofrece la posibilidad de aumentar tu conversión rate con financiones en cuotas exclusivas para clientes PowerPay. 

### Instalación

```sh
$ composer require improntus/module-powerpay
$ php bin/magento module:enable Improntus_PowerPay --clear-static-content
$ php bin/magento setup:upgrade
$ php bin/magento setup:static-content:deploy
```

### Configuraciones
Configuraciones disponibles en Tiendas>Configuracion>Métodos de Pago>Powerpay

### Contemplaciones
Los widgets de Powerpay son cargados desde librerías externas por lo que el módulo contiene un csp_whitelist.xml con excepciones para los dominios requeridos.

## Autor
Pablo Algranati
