<?php

namespace App\Tests\Api;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
  /**
   * @var string
   */
  private static string $URI = '/api/user';

  /**
   * @var string
   */
  private static string $TOKEN_TYPE = 'Bearer ';

  /**
   * @var string
   */
  private static string $TEST_EMAIL = 'john69683@gmail.com';

  /**
   * @var string
   */
  private static string $NOT_VALID_EMAIL = 'john@gmail.ru';

  /**
   * @var string
   */
  private static string $NAME = 'Api';

  /**
   * @var string
   */
  private static string $PASSWORD = '123456';

  /**
   * @var User
   */
  private static User $API_USER;

  /**
   * @var User
   */
  private static User $TEST_USER;

  /**
   * @var string
   */
  private static string $ROLE_GRANTED = 'ROLE_API';

  /**
   * @return void
   */
  public function testPostEmailNotUnique(): void
  {
    $client = static::createClient([
      'headers' => [ 'Content-Type' => 'application/json']
    ]);

    self::$API_USER = $this->createApiRole();

    $client->request( 'POST', self::$URI, [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ], json_encode([
        "email" => self::$NOT_VALID_EMAIL, "name" => self::$NAME, "password" => self::$PASSWORD
      ])
    );

    $this->assertResponseStatusCodeSame(403);
  }

  /**
   * @return void
   */
  public function testPost(): void
  {
    $client = static::createClient([
      'headers' => [ 'Content-Type' => 'application/json']
    ]);

    $client->request( 'POST', self::$URI, [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ], json_encode([
        "email" => self::$TEST_EMAIL, "name" => self::$NAME, "password" => self::$PASSWORD
      ])
    );

    self::$TEST_USER = $this->getUserByEmail(self::$TEST_EMAIL);

    $this->assertResponseStatusCodeSame(201);
  }

  /**
   * @return void
   */
  public function testPathNotValidId(): void
  {
    $client = static::createClient([
      'headers' => [ 'Content-Type' => 'application/json' ]
    ]);

    $client->request( 'PATCH', self::$URI .'/'. (self::$TEST_USER->getId() + 1), [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ], json_encode([
        "email" => self::$TEST_EMAIL . 'Test', "name" => self::$NAME . 'Test', "password" => self::$PASSWORD . 'Test'
      ])
    );

    $this->assertResponseStatusCodeSame(404);
  }

  /**
   * @return void
   */
  public function testPatch(): void
  {
    $client = static::createClient([
      'headers' => [ 'Content-Type' => 'application/json' ]
    ]);

    $client->request( 'PATCH', self::$URI .'/'. self::$TEST_USER->getId(), [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ], json_encode([
        "email" => self::$TEST_EMAIL . 'Test', "name" => self::$NAME . 'Test', "password" => self::$PASSWORD . 'Test'
      ])
    );

    $this->assertResponseIsSuccessful();
  }

  /**
   * @return void
   */
  public function testGetNotValidId(): void
  {
    $client = static::createClient();

    $client->request( 'GET', self::$URI .'/'. (self::$TEST_USER->getId() + 1), [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ]);

    $this->assertResponseStatusCodeSame(404);
  }

  /**
   * @return void
   */
  public function testGet(): void
  {
    $client = static::createClient();

    $client->request( 'GET', self::$URI .'/'. self::$TEST_USER->getId(), [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ]);

    $this->assertResponseIsSuccessful();
  }

  /**
   * @return void
   */
  public function testGetPaginator(): void
  {
    $client = static::createClient();

    $client->request( 'GET', self::$URI .'?limitPerPage=1&page=1', [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ]);

    $this->assertResponseIsSuccessful();
  }

  /**
   * @return void
   */
  public function testGetUserVerified(): void
  {
    $client = static::createClient();

    $client->request( 'GET', self::$URI .'?limitPerPage=1&page=1&verified=true', [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ]);

    $this->assertResponseIsSuccessful();
  }

  /**
   * @return void
   */
  public function testDeleteNotValidId(): void
  {
    $client = static::createClient();

    $client->request( 'DELETE', self::$URI .'/'. (self::$TEST_USER->getId() + 1), [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ]);

    $this->assertResponseStatusCodeSame(404);
  }

  /**
   * @return void
   */
  public function testDelete(): void
  {
    $client = static::createClient();

    $client->request( 'DELETE', self::$URI .'/'. self::$TEST_USER->getId(), [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ]);

    $this->assertResponseStatusCodeSame(204);

  }

  /**
   * @return void
   */
  public function testDeleteTestingData(): void
  {
    $client = static::createClient();

    $client->request( 'DELETE', self::$URI .'/'. self::$API_USER->getId(), [], [], [
      'HTTP_AUTHORIZATION' => self::$TOKEN_TYPE . self::$API_USER->getApiToken(),
    ]);

    $this->assertResponseStatusCodeSame(204);
  }

  /**
   * @param string $email
   * @return User
   */
  private function getUserByEmail(string $email): User
  {
    $userService = static::getContainer()->get(UserService::class);

    return $userService->getUserByEmail($email);
  }

  /**
   * @return User
   */
  private function createApiRole(): User
  {
    $userService = static::getContainer()->get(UserService::class);

    return $userService->createUser(
      self::$NOT_VALID_EMAIL,
      'Admin',
      self::$PASSWORD,
      true,
      self::$ROLE_GRANTED
    );
  }
}
