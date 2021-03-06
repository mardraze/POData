<?php

namespace UnitTests\POData\UriProcessor\UriProcessorNew;

use POData\Common\ODataConstants;
use POData\Common\Url;
use POData\Common\Version;
use POData\Configuration\IServiceConfiguration;
use POData\IService;
use POData\OperationContext\HTTPRequestMethod;
use POData\OperationContext\IHTTPRequest;
use POData\OperationContext\IOperationContext;
use POData\OperationContext\ServiceHost;
use POData\Providers\Metadata\ResourceComplexType;
use POData\Providers\Metadata\ResourceEntityType;
use POData\Providers\Metadata\ResourceFunctionType;
use POData\Providers\Metadata\ResourcePrimitiveType;
use POData\Providers\Metadata\ResourceProperty;
use POData\Providers\Metadata\ResourcePropertyKind;
use POData\Providers\Metadata\ResourceSet;
use POData\Providers\Metadata\ResourceSetWrapper;
use POData\Providers\Metadata\ResourceStreamInfo;
use POData\Providers\Metadata\ResourceType;
use POData\Providers\Metadata\ResourceTypeKind;
use POData\Providers\Metadata\Type\Int32;
use POData\Providers\Metadata\Type\IType;
use POData\Providers\ProvidersWrapper;
use POData\UriProcessor\RequestDescription;
use POData\UriProcessor\UriProcessor;
use POData\UriProcessor\UriProcessorNew;
use UnitTests\POData\TestCase;
use Mockery as m;

class ExecutePostTest extends TestCase
{
    public function testExecutePostOnSingleWithNoData()
    {
        $baseUrl = new Url('http://localhost/odata.svc');
        $reqUrl = new Url('http://localhost/odata.svc/customers(id=1)');

        $host = m::mock(ServiceHost::class);
        $host->shouldReceive('getAbsoluteRequestUri')->andReturn($reqUrl);
        $host->shouldReceive('getAbsoluteServiceUri')->andReturn($baseUrl);
        $host->shouldReceive('getRequestVersion')->andReturn('1.0');
        $host->shouldReceive('getRequestMaxVersion')->andReturn('3.0');
        $host->shouldReceive('getQueryStringItem')->andReturn(null);
        $host->shouldReceive('getRequestContentType')->andReturn(ODataConstants::FORMAT_ATOM)->atLeast(2);

        $request = m::mock(IHTTPRequest::class);
        $request->shouldReceive('getMethod')->andReturn(HTTPRequestMethod::POST());
        $request->shouldReceive('getAllInput')->andReturn(null);

        $context = m::mock(IOperationContext::class);
        $context->shouldReceive('incomingRequest')->andReturn($request);

        $iType = m::mock(IType::class);
        $iType->shouldReceive('isCompatibleWith')->andReturn(true)->atLeast(2);

        $keyProp = m::mock(ResourceProperty::class);
        $keyProp->shouldReceive('getInstanceType')->andReturn($iType);

        $resourceType = m::mock(ResourceType::class);
        $resourceType->shouldReceive('getName')->andReturn('Customer');
        $resourceType->shouldReceive('getResourceTypeKind')->andReturn(ResourceTypeKind::ENTITY);
        $resourceType->shouldReceive('getKeyProperties')->andReturn(['id' => $keyProp])->atLeast(2);
        $resourceType->shouldReceive('getInstanceType->newInstance')->andReturn(new \stdClass())->atLeast(2);

        $result = 'eins';

        $resourceSet = m::mock(ResourceSetWrapper::class);
        $resourceSet->shouldReceive('getResourceType')->andReturn($resourceType);
        $resourceSet->shouldReceive('checkResourceSetRightsForRead')->andReturnNull()->atLeast(2);
        $resourceSet->shouldReceive('hasNamedStreams')->andReturn(false);
        $resourceSet->shouldReceive('hasBagProperty')->andReturn(false);
        $resourceSet->shouldReceive('getResourceSetPageSize')->andReturn(200);

        $wrapper = m::mock(ProvidersWrapper::class);
        $wrapper->shouldReceive('resolveSingleton')->andReturn(null);
        $wrapper->shouldReceive('resolveResourceSet')->andReturn($resourceSet);
        $wrapper->shouldReceive('getResourceFromResourceSet')->andReturn($result)->never();
        $wrapper->shouldReceive('createResourceforResourceSet')->withAnyArgs()->andReturnNull()->never();

        $config = m::mock(IServiceConfiguration::class);
        $config->shouldReceive('getMaxDataServiceVersion')->andReturn(new Version(3, 0));

        $service = m::mock(IService::class);
        $service->shouldReceive('getHost')->andReturn($host);
        $service->shouldReceive('getProvidersWrapper')->andReturn($wrapper);
        $service->shouldReceive('getOperationContext')->andReturn($context);
        $service->shouldReceive('getConfiguration')->andReturn($config);

        $original = UriProcessor::process($service);
        $remix = UriProcessorNew::process($service);

        $expected = null;
        $expectedClass = null;
        $actual = null;
        $actualClass = null;

        try {
            $original->execute();
        } catch (\Exception $e) {
            $expectedClass = get_class($e);
            $expected = $e->getMessage();
        }
        try {
            $remix->execute();
        } catch (\Exception $e) {
            $actualClass = get_class($e);
            $actual = $e->getMessage();
        }
        $this->assertEquals($expectedClass, $actualClass);
        $this->assertEquals($expected, $actual);
    }

    public function testExecutePostOnSingleWithSomeData()
    {
        $baseUrl = new Url('http://localhost/odata.svc');
        $reqUrl = new Url('http://localhost/odata.svc/customers(id=1)');

        $host = m::mock(ServiceHost::class);
        $host->shouldReceive('getAbsoluteRequestUri')->andReturn($reqUrl);
        $host->shouldReceive('getAbsoluteServiceUri')->andReturn($baseUrl);
        $host->shouldReceive('getRequestVersion')->andReturn('1.0');
        $host->shouldReceive('getRequestMaxVersion')->andReturn('3.0');
        $host->shouldReceive('getQueryStringItem')->andReturn(null);
        $host->shouldReceive('getRequestContentType')->andReturn(ODataConstants::FORMAT_ATOM)->atLeast(2);

        $request = m::mock(IHTTPRequest::class);
        $request->shouldReceive('getMethod')->andReturn(HTTPRequestMethod::POST());
        $request->shouldReceive('getAllInput')->andReturn(['a', 'b', 'c']);

        $context = m::mock(IOperationContext::class);
        $context->shouldReceive('incomingRequest')->andReturn($request);

        $iType = m::mock(IType::class);
        $iType->shouldReceive('isCompatibleWith')->andReturn(true)->atLeast(2);

        $keyProp = m::mock(ResourceProperty::class);
        $keyProp->shouldReceive('getInstanceType')->andReturn($iType);

        $resourceType = m::mock(ResourceType::class);
        $resourceType->shouldReceive('getName')->andReturn('Customer');
        $resourceType->shouldReceive('getResourceTypeKind')->andReturn(ResourceTypeKind::ENTITY);
        $resourceType->shouldReceive('getKeyProperties')->andReturn(['id' => $keyProp])->atLeast(2);
        $resourceType->shouldReceive('getInstanceType->newInstance')->andReturn(new \stdClass())->atLeast(2);

        $result = 'eins';

        $resourceSet = m::mock(ResourceSetWrapper::class);
        $resourceSet->shouldReceive('getResourceType')->andReturn($resourceType);
        $resourceSet->shouldReceive('checkResourceSetRightsForRead')->andReturnNull()->atLeast(2);
        $resourceSet->shouldReceive('hasNamedStreams')->andReturn(false);
        $resourceSet->shouldReceive('hasBagProperty')->andReturn(false);
        $resourceSet->shouldReceive('getResourceSetPageSize')->andReturn(200);

        $wrapper = m::mock(ProvidersWrapper::class);
        $wrapper->shouldReceive('resolveSingleton')->andReturn(null);
        $wrapper->shouldReceive('resolveResourceSet')->andReturn($resourceSet);
        $wrapper->shouldReceive('getResourceFromResourceSet')->andReturn($result)->never();
        $wrapper->shouldReceive('createResourceforResourceSet')->with($resourceSet, m::any(), ['a', 'b', 'c'])
            ->andReturn('polizei')->twice();

        $config = m::mock(IServiceConfiguration::class);
        $config->shouldReceive('getMaxDataServiceVersion')->andReturn(new Version(3, 0));

        $service = m::mock(IService::class);
        $service->shouldReceive('getHost')->andReturn($host);
        $service->shouldReceive('getProvidersWrapper')->andReturn($wrapper);
        $service->shouldReceive('getOperationContext')->andReturn($context);
        $service->shouldReceive('getConfiguration')->andReturn($config);

        $original = UriProcessor::process($service);
        $remix = UriProcessorNew::process($service);

        $original->execute();
        $remix->execute();

        $origSegment = $original->getRequest()->getLastSegment();
        $remixSegment = $remix->getRequest()->getLastSegment();
        $this->assertEquals('polizei', $origSegment->getResult());
        $this->assertEquals('polizei', $remixSegment->getResult());
    }
}
