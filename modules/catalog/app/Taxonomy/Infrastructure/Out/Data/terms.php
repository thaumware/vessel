<?php

return [
    'vocabularies' => [
        [
            'id' => 'vocab-1',
            'name' => 'Product Categories',
            'slug' => 'product-categories',
            'description' => 'Categorization of products for inventory management',
            'workspace_id' => null,
        ],
        [
            'id' => 'vocab-2',
            'name' => 'Brands',
            'slug' => 'brands',
            'description' => 'Product brands and manufacturers',
            'workspace_id' => null,
        ],
    ],
    'terms' => [
        // Product Categories
        [
            'id' => 'term-1',
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic devices and accessories',
            'vocabulary_id' => 'vocab-1',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-2',
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'description' => 'Mobile phones and smartphones',
            'vocabulary_id' => 'vocab-1',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-3',
            'name' => 'Laptops',
            'slug' => 'laptops',
            'description' => 'Portable computers',
            'vocabulary_id' => 'vocab-1',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-4',
            'name' => 'Clothing',
            'slug' => 'clothing',
            'description' => 'Apparel and fashion items',
            'vocabulary_id' => 'vocab-1',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-5',
            'name' => 'Men\'s Clothing',
            'slug' => 'mens-clothing',
            'description' => 'Clothing for men',
            'vocabulary_id' => 'vocab-1',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-6',
            'name' => 'Women\'s Clothing',
            'slug' => 'womens-clothing',
            'description' => 'Clothing for women',
            'vocabulary_id' => 'vocab-1',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-7',
            'name' => 'Shirts',
            'slug' => 'shirts',
            'description' => 'Shirt category',
            'vocabulary_id' => 'vocab-1',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-8',
            'name' => 'Pants',
            'slug' => 'pants',
            'description' => 'Pants and trousers',
            'vocabulary_id' => 'vocab-1',
            'workspace_id' => null,
        ],

        // Brands
        [
            'id' => 'term-9',
            'name' => 'Apple',
            'slug' => 'apple',
            'description' => 'Apple Inc. products',
            'vocabulary_id' => 'vocab-2',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-10',
            'name' => 'Samsung',
            'slug' => 'samsung',
            'description' => 'Samsung Electronics products',
            'vocabulary_id' => 'vocab-2',
            'workspace_id' => null,
        ],
        [
            'id' => 'term-11',
            'name' => 'Nike',
            'slug' => 'nike',
            'description' => 'Nike sportswear and footwear',
            'vocabulary_id' => 'vocab-2',
            'workspace_id' => null,
        ],
    ],
    'term_relations' => [
        // Electronics hierarchy
        [
            'id' => 'rel-1',
            'from_term_id' => 'term-2', // Smartphones
            'to_term_id' => 'term-1',   // Electronics (parent)
            'relation_type' => 'parent',
            'depth' => 1,
        ],
        [
            'id' => 'rel-2',
            'from_term_id' => 'term-3', // Laptops
            'to_term_id' => 'term-1',   // Electronics (parent)
            'relation_type' => 'parent',
            'depth' => 1,
        ],

        // Clothing hierarchy
        [
            'id' => 'rel-3',
            'from_term_id' => 'term-5', // Men's Clothing
            'to_term_id' => 'term-4',   // Clothing (parent)
            'relation_type' => 'parent',
            'depth' => 1,
        ],
        [
            'id' => 'rel-4',
            'from_term_id' => 'term-6', // Women's Clothing
            'to_term_id' => 'term-4',   // Clothing (parent)
            'relation_type' => 'parent',
            'depth' => 1,
        ],
        [
            'id' => 'rel-5',
            'from_term_id' => 'term-7', // Shirts
            'to_term_id' => 'term-5',   // Men's Clothing (parent)
            'relation_type' => 'parent',
            'depth' => 2,
        ],
        [
            'id' => 'rel-6',
            'from_term_id' => 'term-8', // Pants
            'to_term_id' => 'term-5',   // Men's Clothing (parent)
            'relation_type' => 'parent',
            'depth' => 2,
        ],
    ],
];