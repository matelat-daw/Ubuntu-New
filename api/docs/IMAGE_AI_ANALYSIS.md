# 📸 Sistema de Análisis de Imágenes con IA

## Descripción

El sistema utiliza **OpenAI Vision API** (GPT-4o-mini) para analizar automáticamente las imágenes de productos y seleccionar la mejor como imagen principal.

## Características

✅ **Análisis automático** de calidad de imagen  
✅ **Selección inteligente** de imagen principal  
✅ **Evaluación multi-criterio**: composición, iluminación, enfoque, fondo  
✅ **Puntuación 0-100** para cada imagen  
✅ **Fallback sin IA**: análisis básico por propiedades de imagen  
✅ **Almacenamiento de análisis** en base de datos (JSON)

## Configuración

### 1. Obtener API Key de OpenAI

1. Regístrate en [OpenAI Platform](https://platform.openai.com/)
2. Ve a [API Keys](https://platform.openai.com/api-keys)
3. Crea una nueva API key
4. Copia la key

### 2. Configurar en la API

**Opción A: Variable de entorno** (Recomendado)
```bash
export OPENAI_API_KEY="sk-proj-tu-api-key-aqui"
```

**Opción B: Archivo config.php**
```php
define('OPENAI_API_KEY', 'sk-proj-tu-api-key-aqui');
```

### 3. Verificar instalación

Las dependencias ya están instaladas:
- ✅ `openai-php/client` v0.18.0
- ✅ `phpoffice/phpspreadsheet` v5.2.0

## Uso

### Análisis Automático al Subir Productos

Cuando un vendedor sube múltiples imágenes de un producto:

```php
$analyzer = new ImageAIAnalyzer();

// Analizar todas las imágenes
$result = $analyzer->analyzeBatch(
    $imagePaths,
    'Nombre del Producto',
    'Descripción del producto'
);

// $result contiene:
// - analyses: array con análisis de cada imagen
// - best_image_index: índice de la mejor imagen
// - best_score: puntuación de la mejor imagen
```

### Criterios de Evaluación

La IA evalúa cada imagen con estos criterios:

1. **Calidad general** (0-100 puntos)
   - Resolución
   - Nitidez
   - Profesionalismo

2. **Composición**
   - Encuadre
   - Centrado del producto
   - Proporciones

3. **Iluminación**
   - Claridad
   - Ausencia de sombras duras
   - Colores naturales

4. **Enfoque**
   - Producto nítido
   - Bien definido

5. **Fondo**
   - Limpio
   - No distrae
   - Apropiado

6. **Visibilidad del producto**
   - Características importantes visibles
   - Detalles claros

### Estructura de Análisis Almacenado

```json
{
  "score": 85,
  "quality": "alta",
  "composition": "Producto bien centrado y encuadrado",
  "lighting": "Iluminación uniforme y natural",
  "focus": "Producto nítido con buen detalle",
  "background": "Fondo blanco limpio y profesional",
  "product_visibility": "Todas las características visibles",
  "recommendation": "usar como principal",
  "width": 1920,
  "height": 1080,
  "file_size": 524288
}
```

## Tablas de Base de Datos

### `products`
```sql
- seller_id (INT) - ID del vendedor
- name, description, price, stock, etc.
```

### `product_images`
```sql
- product_id (INT)
- path (VARCHAR) - Ruta de la imagen
- width (INT) - Ancho en píxeles
- height (INT) - Alto en píxeles
- file_size (INT) - Tamaño en bytes
- is_primary (TINYINT) - 1 = imagen principal
- ai_score (DECIMAL) - Puntuación de IA (0-100)
- ai_analysis (JSON) - Análisis completo
- sort_order (INT) - Orden de visualización
```

### `sales`
```sql
- order_id, product_id, seller_id
- quantity, unit_price, total_price
- commission_rate, commission_amount
- sale_date
```

## Modo Fallback (Sin API Key)

Si no hay API key configurada, el sistema usa análisis básico:

```php
$result = $analyzer->analyzeImageBasic($imagePath);

// Evalúa:
// - Resolución (puntos por megapíxeles)
// - Ratio de aspecto (preferencia por imágenes cuadradas)
// - Tamaño de archivo (ni muy pequeño ni muy grande)
```

## Costos de OpenAI

- **Modelo**: gpt-4o-mini (más económico)
- **Costo aproximado**: ~$0.01 por 10 imágenes analizadas
- **Tokens**: ~500 tokens por análisis

## Ejemplo de Implementación

Ver archivo: `/api/controllers/products/create.php`

```php
// 1. Subir imágenes al servidor
// 2. Analizar con IA
$analyzer = new ImageAIAnalyzer();
$analysis = $analyzer->analyzeBatch($imagePaths, $productName, $description);

// 3. Guardar imágenes en BD con análisis
foreach ($imagePaths as $index => $path) {
    $isPrimary = ($index === $analysis['best_image_index']) ? 1 : 0;
    $aiData = $analysis['analyses'][$index];
    
    $product->addImage(
        $path,
        $aiData['width'],
        $aiData['height'],
        $aiData['file_size'],
        $productName,
        $isPrimary,
        $aiData['score'],
        $aiData
    );
}
```

## Ventajas del Sistema

✅ **Automatización**: No requiere selección manual  
✅ **Consistencia**: Criterios objetivos y uniformes  
✅ **Calidad**: Mejora la presentación de productos  
✅ **Velocidad**: Análisis en segundos  
✅ **Escalabilidad**: Analiza cientos de productos  
✅ **Trazabilidad**: Guarda análisis para auditoría

## Limitaciones

⚠️ **API Key requerida**: Para análisis completo con IA  
⚠️ **Costos**: Uso de API de OpenAI (muy bajo)  
⚠️ **Tamaño de imagen**: Máximo 5MB por imagen  
⚠️ **Formatos**: JPEG, PNG, WebP

## Soporte

Para configuración o problemas, revisar:
- `/api/classes/ImageAIAnalyzer.php`
- `/api/config/config.php`
- Logs en `/api/logs/error.log`
