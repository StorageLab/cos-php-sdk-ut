<?php

namespace Qcloud\Cos\Tests;

use Qcloud\Cos\Client;
use Qcloud\Cos\Exception\CosException;

class BucketTest extends \PHPUnit_Framework_TestCase
{
    private $cosClient;
    private $bucket;
    protected function setUp()
    {
        $this->bucket = getenv('COS_BUCKET');
        TestHelper::nuke($this->bucket);
        $this->cosClient = new Client(array('region' => getenv('COS_REGION'),
            'credentials' => array(
                'appId' => getenv('COS_APPID'),
                'secretId' => getenv('COS_KEY'),
                'secretKey' => getenv('COS_SECRET'))));
        sleep(3);
    }

    protected function tearDown()
    {
        TestHelper::nuke($this->bucket);
    }

    /**********************************
     * TestBucket
     **********************************/

    /*
     * put bucket,bucket已经存在
     * BucketAlreadyOwnedByYou
     * 409
     */
    public function testCreateExistingBucket()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
        } catch (\Exception $e) {
            $this->assertTrue($e->getExceptionCode() === 'BucketAlreadyOwnedByYou' && $e->getStatusCode() === 409);
        }
    }

    /*
     * put bucket,bucket名称非法
     * InvalidBucketName
     * 400
     */
    public function testCreateInvalidBucket()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => 'qwe_213'));
        } catch (\Exception $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidBucketName' && $e->getStatusCode() === 400);
        }
    }

    /*
     * put bucket，设置bucket公公权限为private
     * 200
     */
    public function testCreatePrivateBucket()
    {
        try {
            $this->cosClient->createBucket(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'=>'private'
                ));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket，设置bucket公公权限为public-read
     * 200
     */
    public function testCreatePublicReadBucket()
    {
        try {
            $this->cosClient->createBucket(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'=>'public-read'
                ));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket，公共权限非法
     * InvalidArgument
     * 400
     */
    public function testCreateInvalidACLBucket()
    {
        try {
            $this->cosClient->createBucket(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'=>'public'
                ));
        } catch (\Exception $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidArgument' && $e->getStatusCode() === 400);
        }
    }

    /*
     * put bucket acl，设置bucket公共权限为private
     * 200
     */
    public function testPutBucketAclPrivate()
    {
        try {

            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'=>'private'
                ));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，设置bucket公共权限为public-read
     * 200
     */
    public function testPutBucketAclPublicRead()
    {
        try {

            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'=>'public-read'
                ));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，公共权限非法
     * InvalidArgument
     * 400
     */
    public function testPutBucketAclInvalid()
    {
        try {
            $this->cosClient->createBucket(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'=>'public'
                ));
        } catch (\Exception $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidArgument' && $e->getStatusCode() === 400);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限为grant-read
     * 200
     */
    public function testPutBucketAclReadToUser()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantRead' => 'id="qcs::cam::uin/327874225:uin/327874225"'));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限为grant-write
     * 200
     */
    public function testPutBucketAclWriteToUser()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantWrite' => 'id="qcs::cam::uin/327874225:uin/327874225"'));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限为grant-full-control
     * 200
     */
    public function testPutBucketAclFullToUser()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantFullControl' => 'id="qcs::cam::uin/327874225:uin/327874225"'));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限，同时授权给多个账户
     * 200
     */
    public function testPutBucketAclToUsers()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/327874225",id="qcs::cam::uin/327874225:uin/327874225",id="qcs::cam::uin/327874225:uin/327874225"'));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限，授权给子账号
     * 200
     */
    public function testPutBucketAclToSubuser()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/327874225"'));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限，同时指定read、write和fullcontrol
     * 200
     */
    public function testPutBucketAclReadWriteFull()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantRead' => 'id="qcs::cam::uin/123:uin/123"',
                'GrantWrite' => 'id="qcs::cam::uin/2779643970:uin/327874225"',
                'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/327874225"',));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限，grant值非法
     * InvalidArgument
     * 400
     */
    public function testPutBucketAclInvalidGrant()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantFullControl' => 'id="qcs::camuin/321023:uin/327874225"',));
        } catch (\Exception $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidArgument' && $e->getStatusCode() === 400);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限，通过body方式授权
     * 200
     */
    public function testPutBucketAclByBody()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' => $this->bucket,
                'Grants' => array(
                    array(
                        'Grantee' => array(
                            'DisplayName' => 'qcs::cam::uin/327874225:uin/327874225',
                            'ID' => 'qcs::cam::uin/327874225:uin/327874225',
                            'Type' => 'CanonicalUser',
                        ),
                        'Permission' => 'FULL_CONTROL',
                    ),
                    // ... repeated
                ),
                'Owner' => array(
                    'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                    'ID' => 'qcs::cam::uin/2779643970:uin/2779643970',
                )));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，设置bucket账号权限，通过body方式授权给anyone
     * 200
     */
    public function testPutBucketAclByBodyToAnyone()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' => $this->bucket,
                'Grants' => array(
                    array(
                        'Grantee' => array(
                            'DisplayName' => 'qcs::cam::anyone:anyone',
                            'ID' => 'qcs::cam::anyone:anyone',
                            'Type' => 'CanonicalUser',
                        ),
                        'Permission' => 'FULL_CONTROL',
                    ),
                    // ... repeated
                ),
                'Owner' => array(
                    'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                    'ID' => 'qcs::cam::uin/2779643970:uin/2779643970',
                )));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket acl，bucket不存在
     * NoSuchBucket
     * 404
     */
    public function testPutBucketAclBucketNonexisted()
    {
        try {
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantFullControl' => 'id="qcs::cam::uin/321023:uin/327874225"',));
        } catch (\Exception $e) {
//            echo($e->getExceptionCode());
//            echo($e->getStatusCode());
            $this->assertTrue($e->getExceptionCode() === 'NoSuchBucket' && $e->getStatusCode() === 404);
        }
    }

    /*
     * put bucket acl，覆盖设置
     * x200
     */
    public function testPutBucketAclCover()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/327874225"',
                'GrantRead' => 'id="qcs::cam::uin/2779643970:uin/327874225"',
                'GrantWrite' => 'id="qcs::cam::uin/2779643970:uin/327874225"'));
            $this->cosClient->PutBucketAcl(array(
                'Bucket' =>  $this->bucket,
                'GrantWrite' => 'id="qcs::cam::uin/2779643970:uin/327874225"'));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * get bucket,bucket为空
     * 200
     */
    public function testGetBucketEmpty()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->ListObjects(array(
                'Bucket' =>  $this->bucket));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * get bucket，bucket不存在
     * NoSuchBucket
     * 404
     */
    public function testGetBucketNonexisted()
    {
        try {
            $this->cosClient->ListObjects(array(
                'Bucket' =>  $this->bucket,));
        } catch (\Exception $e) {
            $this->assertTrue($e->getExceptionCode() === 'NoSuchBucket' && $e->getStatusCode() === 404);
        }
    }


    /*
     * put bucket,bucket名称带有-
     * 200
     */
    public function testPutBucket2()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket.'-12345'));
            $this->cosClient->deleteBucket(array('Bucket' => $this->bucket.'-12345'));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket,bucket名称带有两个-
     * 200
     */
    public function testPutBucket3()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket.'-12333-4445'));
            $this->cosClient->deleteBucket(array('Bucket' => $this->bucket.'-12333-4445'));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put bucket，bucket名称以-开头
     * InvalidBucketName
     * 400
     */
    public function testPutBucket4()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => '-12333-4445'.$this->bucket));
            $this->cosClient->deleteBucket(array('Bucket' => '-12333-4445'.$this->bucket));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e);
        }
    }


    /**********************************
     * TestObject
     **********************************/

    /*
     * 上传文件Bucket不存在
     * NoSuchBucket
     * 404
     */
    public function testPutObjectIntoNonexistedBucket() {
        try {
            $this->cosClient->putObject(array(
                'Bucket' => $this->bucket, 'Key' => 'hello.txt', 'Body' => 'Hello World'));
        } catch (CosException $e) {
            $this->assertTrue($e->getExceptionCode() === 'NoSuchBucket');
            $this->assertTrue($e->getStatusCode() === 404);
        }
    }


    /*
     * 上传小文件
     * 200
     */
    public function testUploadSmallObject() {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->upload($this->bucket, '你好.txt', 'Hello World');
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * 上传空文件
     * 200
     */
    public function testPutObjectEmpty() {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->upload($this->bucket, '你好.txt', '123');
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * 上传已存在的文件
     * 200
     */
    public function testPutObjectExisted() {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->upload($this->bucket, '你好.txt', '1234124');
            $this->cosClient->upload($this->bucket, '你好.txt', '请二位qwe');
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put object，请求头部携带自定义头部x-cos-meta-
     * 200
     */
    public function testPutObjectMeta() {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->putObject(array(
                'Bucket' => $this->bucket,
                'Key' => '你好.txt',
                'Body' => '1234124',
                'Metadata' => array(
                     'lew' => str_repeat('a', 1 * 1024),
            )));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * put object，请求头部携带自定义头部x-cos-meta-
     * KeyTooLong
     * 400
     */
    public function testPutObjectMeta2K() {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->putObject(array(
                'Bucket' => $this->bucket,
                'Key' => '你好.txt',
                'Body' => '1234124',
                'Metadata' => array(
                    'lew' => str_repeat('a', 3 * 1024),
                )));
        } catch (\Exception $e) {
//            echo($e->getExceptionCode());
//            echo($e->getStatusCode());
            $this->assertTrue($e->getExceptionCode() === 'KeyTooLong' && $e->getStatusCode() === 400);
        }
    }

    /*
     * 上传复杂文件名的文件
     * 200
     */
    public function testUploadComplexObject() {
        try {
            $result = $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->upload($this->bucket, '→↓←→↖↗↙↘! \"#$%&\'()*+,-./0123456789:;<=>@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~', 'Hello World');
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * 上传大文件
     * 200
     */
    public function testUploadLargeObject() {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->upload($this->bucket, 'hello.txt', str_repeat('a', 9 * 1024 * 1024));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

    /*
     * 下载文件
     * 200
     */
    public function testGetObject() {
        try {
            $this->cosClient->createBucket(array('Bucket' => $this->bucket));
            $this->cosClient->upload($this->bucket, '你好.txt', 'Hello World');
            $this->cosClient->getObject(array(
                'Bucket' => $this->bucket,
                'Key' => '你好.txt',));
        } catch (\Exception $e) {
            $this->assertFalse(true, $e);
        }
    }

}