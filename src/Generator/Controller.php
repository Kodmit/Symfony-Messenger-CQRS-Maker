<?php

namespace Kodmit\MessengerCqrsGeneratorBundle\Generator;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractGenerator implements GeneratorInterface
{
    public const CREATE = 'Create';
    public const DELETE = 'Delete';
    public const UPDATE = 'Update';

    private const AVAILABLE_SCOPES = [self::CREATE, self::DELETE, self::UPDATE];

    public function generate(string $scope = null): array
    {
        $filePath = $this->initFile($scope);

        if (null === $scope) {
            foreach (self::AVAILABLE_SCOPES as $availableScope) {
                $this->appendMethod($availableScope);
            }
            return [$filePath];
        }

        if (false === in_array($scope, self::AVAILABLE_SCOPES)) {
            throw new \DomainException(sprintf('invalid scope "%s"', $scope));
        }

        $this->appendMethod($scope);

        return [$filePath];
    }

    private function appendMethod(string $scope): void
    {
        $withId = false;
        $isDelete = false;

        if (self::CREATE !== $scope) {
            $withId = true;
        }

        if (self::DELETE === $scope) {
            $isDelete = true;
        }

        $httpPayload = '';
        $filePath = sprintf('%s/Controller/%sController.php', self::APP_ROOT, $this->className);

        $lines = file($filePath);

        $linesCount = count($lines);
        $closingLine = $linesCount; // Line for code writing

        foreach (array_reverse($lines) as $lineNumber => $lineText) {
            if (false !== strpos($lineText, '}')) {
                $closingLine = $linesCount - $lineNumber - 1;
                break;
            }
        }

        if (true === $withId) {
            $httpPayload .= sprintf('$data[\'%sId\'],' . "\n", strtolower($this->className));
        }

        foreach ($this->reflection->getProperties() as $property) {
            if (true === $isDelete) {
                break;
            }

            if (true === in_array($property->getName(), self::SKIPPED_PROPERTIES) || 'id' === $property->getName()) {
                continue;
            }

            if (false === empty($httpPayload)) {
                $httpPayload .= '            ';
            }

            $httpPayload .= sprintf('$data[\'%s\'],' . "\n", $property->getName());
        }

        $httpPayload = rtrim($httpPayload, "\n");
        $httpPayload = rtrim($httpPayload, ',');

        $lines[$closingLine + 1] = $lines[$closingLine];

        $method = sprintf('
     /**
     * @Route("/%1$ss", method={"%6$s"})
     */
    public function %5$s%2$s(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $envelope = $this->commandBus->dispatch(new %4$s%2$s(
           %3$s
        ));

        $user = $envelope->last(HandledStamp::class)->getResult();

        return $this->json(
            $user,
            JsonResponse::%7$s
        );
    }
        ',
            strtolower($this->className),
            $this->className,
            $httpPayload,
            $scope,
            strtolower($scope),
            self::getHttpVerbForScope($scope),
            self::getHttpResponseCodeForScope($scope)
        );

        $lines[$closingLine] = $method;

        file_put_contents( $filePath , implode( "", $lines ) );
    }

    private function initFile(?string $scope): string
    {
        $filePath = sprintf('%s/Controller/%sController.php', self::APP_ROOT, $this->className);
        touch($filePath);

        $extraClasses = '';

        if (null !== $scope) {
            $extraClasses = sprintf('use App\\Action\\%1$s\\%2$s%1$s;', $this->className, $scope);
        } else {
            foreach (self::AVAILABLE_SCOPES as $availableScope) {
                $extraClasses .= sprintf('use App\\Action\\%1$s\\%2$s%1$s;' . "\n", $this->className, $availableScope);
            }
        }

        $data = sprintf('<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use App\Repository\%1$sRepository;
%3$s

class %1$sController extends AbstractController
{
    private MessageBusInterface $commandBus;
    private %1$sRepository $%2$sRepository;

    public function __construct(MessageBusInterface $commandBus, %1$sRepository $%2$sRepository)
    {
        $this->commandBus = $commandBus;
        $this->%2$sRepository = $%2$sRepository;
    }
    
    /**
     * @Route("/users", method={"GET"})
     */
    public function getUsers(): JsonResponse
    {
        return $this->json(
            $this->userRepository->findAll(),
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @Route("/users/{userId}", method={"GET"})
     */
    public function getUserById(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        
        if (null === $user) {
            return new JsonResponse(sprintf(\'user with id "%%s" not found\', JsonResponse::HTTP_NOT_FOUND));
        }
        
        return $this->json(
            $user,
            JsonResponse::HTTP_OK
        );
    }

}
        ', $this->className, strtolower($this->className), $extraClasses);

        file_put_contents($filePath, $data);

        return self::getHumanReadablePath($filePath);
    }

    private static function getHttpVerbForScope(string $scope): string
    {
        switch ($scope) {
            case self::CREATE:
                return 'POST';
                break;
            case self::DELETE:
                return 'DELETE';
                break;
            case self::UPDATE:
                return 'PUT';
                break;
            default:
                throw new \DomainException('scope not found');
        }
    }

    private static function getHttpResponseCodeForScope(string $scope): string
    {
        switch ($scope) {
            case self::CREATE:
                return 'HTTP_CREATED';
                break;
            case self::DELETE:
                return 'HTTP_NO_CONTENT';
                break;
            case self::UPDATE:
                return 'HTTP_OK';
                break;
            default:
                throw new \DomainException('scope not found');
        }
    }
}