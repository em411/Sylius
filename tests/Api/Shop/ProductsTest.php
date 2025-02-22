<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Tests\Api\Shop;

use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Tests\Api\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ProductsTest extends JsonApiTestCase
{
    /** @test */
    public function it_preserves_query_param_when_redirecting_from_product_slug_to_product_code(): void
    {
        $this->loadFixturesFromFile('product/product_variant_with_original_price.yaml');

        $this->client->request('GET', '/api/v2/shop/products-by-slug/mug?paramName=paramValue', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertEquals('/api/v2/shop/products/MUG?paramName=paramValue', $response->headers->get(('Location')));
        $this->assertResponseCode($response, Response::HTTP_MOVED_PERMANENTLY);
    }

    /** @test */
    public function it_returns_product_with_translations_in_default_locale(): void
    {
        $fixtures = $this->loadFixturesFromFile('product/product_with_many_locales.yaml');

        /** @var ProductInterface $product */
        $product = $fixtures['product_mug'];
        $this->client->request('GET',
            sprintf('/api/v2/shop/products/%s', $product->getCode()),
            [],
            [],
            self::CONTENT_TYPE_HEADER
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'shop/product/get_product_with_default_locale_translation',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_returns_product_with_translations_in_locale_from_header(): void
    {
        $fixtures = $this->loadFixturesFromFile('product/product_with_many_locales.yaml');

        /** @var ProductInterface $product */
        $product = $fixtures['product_mug'];
        $this->client->request('GET',
            sprintf('/api/v2/shop/products/%s', $product->getCode()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json', 'HTTP_ACCEPT_LANGUAGE' => 'de_DE']
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'shop/product/get_product_with_de_DE_locale_translation',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_returns_products_collection(): void
    {
        $this->loadFixturesFromFiles(['product/product_variant_with_original_price.yaml']);

        $this->client->request('GET',
            '/api/v2/shop/products',
            [],
            [],
            self::CONTENT_TYPE_HEADER
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'shop/product/get_products_collection_response',
            Response::HTTP_OK
        );
    }
}
