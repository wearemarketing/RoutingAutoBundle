<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional;

interface RepositoryInterface
{
    public function createBlog();

    public function getObjectManager();

    public function findBlog($blogName);

    public function findRoutesForBlog($blog);

    public function findAutoRoute($url);
}
