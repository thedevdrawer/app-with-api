<?php
/**
* @OA\Info(title="API Live Test", version="1.0")
*   @OA\SecurityScheme(
*       type="http",
*       description=" Use /auth to get the JWT token",
*       name="Authorization",
*       in="header",
*       scheme="bearer",
*       bearerFormat="JWT",
*       securityScheme="bearerAuth",
*   )
*/

require $_SERVER['DOCUMENT_ROOT'].'/api/vendor/autoload.php';

use \Firebase\JWT\JWT;

class Pages {
    private $conn;

    private $key = 'privatekey';

    private $db_table = 'pages';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
    * @OA\Get(path="/api/v1/pages/auth", tags={"Pages"},
    * @OA\Response (response="200", description="Success"),
    * @OA\Response (response="404", description="Not Found"),
    * )
    */

    public function auth(){
        $iat = time();
        $exp = $iat + 60 * 60;
        $payload = array(
            'iss' => 'http://liveapi.local/api', //issuer
            'aud' => 'http://livetest.local/', //audience
            'iat' => $iat, //time JWT was issued
            'exp' => $exp //time JWT expires
        );
        $jwt = JWT::encode($payload, $this->key, 'HS512');
        return array(
            'token'=>$jwt,
            'expires'=>$exp
        );
    }

    /**
    * @OA\Post(path="/api/v1/pages/read", tags={"Pages"},
    *  @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="multipart/form-data",
    *           @OA\Schema(required={"authorization"}, @OA\Property(property="authorization", type="string"))    
    *       )
    *   ),
    * @OA\Response (response="200", description="Success"),
    * @OA\Response (response="404", description="Not Found"),
    * security={ {"bearerAuth":{}}}
    * )
    */
    public function read($auth) {
        //$headers = apache_request_headers();
        if(isset($auth)):
            $token = str_replace('Bearer ', '',$auth);
            try {
                $token = JWT::decode($token, $this->key, array('HS512'));
                $query = "SELECT slug,title FROM " . $this->db_table ." ORDER BY
                    orderid ASC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                return $stmt;
            } catch (\Exception $e) {
                return false;
            }
        else:
            return false;
        endif;
    }

    /**
    * @OA\Post(path="/api/v1/pages/single", tags={"Pages"},
    *   @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="multipart/form-data",
    *           @OA\Schema(required={"slug", "authorization"}, @OA\Property(property="slug", type="string"), @OA\Property(property="authorization", type="string"))    
    *       )
    *   ),
    *   @OA\Response (response="200", description="Success"),
    *   @OA\Response (response="404", description="Not Found"),
    *   security={ {"bearerAuth":{}}}
    * )
    */
    public function single($slug, $auth) {
        //$headers = apache_request_headers();
        if(isset($auth)):
            $token = str_replace('Bearer ', '',$auth);
            try {
                $token = JWT::decode($token, $this->key, array('HS512'));
                $query = "SELECT title,content FROM " . $this->db_table ." WHERE
                    slug = :slug";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":slug", $slug);
                $stmt->execute();
                return $stmt;
            } catch (\Exception $e) {
                return false;
            }
        else:
            return false;
        endif;
    }

    /**
    * @OA\Post(path="/api/v1/pages/create", tags={"Pages"},
    *   @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="json",
    *           @OA\Schema(required={"slug","title", "content", "orderid"},
    *               @OA\Property(property="slug", type="string"),
    *               @OA\Property(property="title", type="string"),    
    *               @OA\Property(property="content", type="string", example="<p>HTML only</p>"),
    *               @OA\Property(property="orderid", type="integer")
    *           )
    *       )
    *   ),
    *   @OA\Response (response="200", description="Success"),
    *   @OA\Response (response="404", description="Not Found"),
    *   security={ {"bearerAuth":{}}}
    * )
    */
    public function create() {
        $headers = apache_request_headers();
        if(isset($headers['Authorization'])):
            $token = str_replace('Bearer ', '',$headers['Authorization']);
            try {
                $token = JWT::decode($token, $this->key, array('HS512'));
                $query = "INSERT INTO " . $this->db_table ."
                SET
                    slug = :slug,
                    orderid = :orderid,
                    title = :title,
                    content = :content";

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(":orderid", $this->orderid);
                $stmt->bindParam(":title", $this->title);
                $stmt->bindParam(":content", $this->content);
                $stmt->bindParam(":slug", $this->slug);

                if($stmt->execute()):
                    if($stmt->rowCount()):
                        $this->createPageHTML($this->slug,$this->title);
                        return true;
                    else:
                        return false;
                    endif;
                else:
                    return false;
                endif;
            } catch (\Exception $e) {
                return false;
            }
        else:
            return false;
        endif;
    }

    /**
    * @OA\Put(path="/api/v1/pages/update", tags={"Pages"},
    *   @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="json",
    *           @OA\Schema(required={"id","title", "content", "orderid"},
    *               @OA\Property(property="id", type="integer", example=2),
    *               @OA\Property(property="title", type="string", example="About Us"),    
    *               @OA\Property(property="content", type="string", example="<p>HTML only</p>"),
    *               @OA\Property(property="orderid", type="integer", example=2)
    *           )
    *       )
    *   ),
    *   @OA\Response (response="200", description="Success"),
    *   @OA\Response (response="404", description="Not Found"),
    *   security={ {"bearerAuth":{}}}
    * )
    */
    public function update() {
        $headers = apache_request_headers();
        if(isset($headers['Authorization'])):
            $token = str_replace('Bearer ', '',$headers['Authorization']);
            try {
                $token = JWT::decode($token, $this->key, array('HS512'));
                $query = "UPDATE " . $this->db_table ."
                SET
                    orderid = :orderid,
                    title = :title,
                    content = :content
                WHERE
                    id = :id";

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(":orderid", $this->orderid);
                $stmt->bindParam(":title", $this->title);
                $stmt->bindParam(":content", $this->content);
                $stmt->bindParam(":id", $this->id);

                if($stmt->execute()):
                    if($stmt->rowCount()):
                        return true;
                    else:
                        return false;
                    endif;
                else:
                    return false;
                endif;
            } catch (\Exception $e) {
                return false;
            }
        else:
            return false;
        endif;
    }

    /**
    * @OA\Delete(path="/api/v1/pages/delete", tags={"Pages"},
    *   @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="json",
    *           @OA\Schema(required={"id"},
    *               @OA\Property(property="id", type="integer"),
    *           )
    *       )
    *   ),
    *   @OA\Response (response="200", description="Success"),
    *   @OA\Response (response="404", description="Not Found"),
    *   security={ {"bearerAuth":{}}}
    * )
    */
    public function delete() {
        $headers = apache_request_headers();
        if(isset($headers['Authorization'])):
            $token = str_replace('Bearer ', '',$headers['Authorization']);
            try {
                $token = JWT::decode($token, $this->key, array('HS512'));
                $query = "SELECT slug from " . $this->db_table ."
                WHERE
                    id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $this->id);
                $stmt->execute();
                if($stmt->rowCount()):
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        $slug = $row['slug'];
                    endwhile;
                    $this->deletePageHTML($slug);
                    $query = "DELETE FROM " . $this->db_table ."
                    WHERE
                        id = :id";

                    $stmt = $this->conn->prepare($query);

                    $stmt->bindParam(":id", $this->id);

                    if($stmt->execute()):
                        if($stmt->rowCount()):
                            return true;
                        else:
                            return false;
                        endif;
                    else:
                        return false;
                    endif;
                else:
                    return false;
                endif;
            } catch (\Exception $e) {
                return false;
            }
        else:
            return false;
        endif;
    }

    function createPageHTML($slug, $title) {
        $file = $_SERVER['DOCUMENT_ROOT'].'/pages/'.$slug.'.template';
        $content = '
        <div class="page">
        <h1></h1>
        <div class="content"></div>
        </div>
        <script src="js/init.js"></script>
        <script> getpage(\''.$slug.'\'); </script>
        ';
        file_put_contents($file, $content);
    }

    function deletePageHTML($slug){
        $file = $_SERVER['DOCUMENT_ROOT'].'/pages/'.$slug.'.template';
        unlink($file);
    }
}