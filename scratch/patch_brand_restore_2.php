<?php

// 1. Patch Product.php
$productFile = 'c:\\wamp64\\www\\prs\\app\\Models\\Product.php';
$productContent = file_get_contents($productFile);

// Add brand to fillable
if (strpos($productContent, "'brand',") === false) {
    $productContent = str_replace(
        "'product_name',\n        'brand_id',",
        "'product_name',\n        'brand',\n        'brand_id',",
        $productContent
    );
}

// Remove custom getBrandAttribute
$productContent = preg_replace(
    '/public function getBrandAttribute\(\)\s*\{\s*return \$this->brandModel \? \$this->brandModel->name : null;\s*\}/s',
    '',
    $productContent
);

file_put_contents($productFile, $productContent);
echo "Product.php patched\n";

// 2. Patch ProductController.php
$controllerFile = 'c:\\wamp64\\www\\prs\\app\\Http\\Controllers\\ProductController.php';
$controllerContent = file_get_contents($controllerFile);

// Store and Update methods
$controllerContent = str_replace(
    '$data[\'brand_id\'] = $brand->id;',
    '$data[\'brand_id\'] = $brand->id;' . "\n" . '            $data[\'brand\'] = trim($request->brand);',
    $controllerContent
);

$controllerContent = str_replace(
    "        } else {\n            \$data['brand_id'] = null;\n        }",
    "        } else {\n            \$data['brand_id'] = null;\n            \$data['brand'] = null;\n        }",
    $controllerContent
);

// Import method
$controllerContent = str_replace(
    "'brand_id' => \$brandId,",
    "'brand' => \$importedBrand,\n                            'brand_id' => \$brandId,",
    $controllerContent
);

file_put_contents($controllerFile, $controllerContent);
echo "ProductController.php patched\n";
