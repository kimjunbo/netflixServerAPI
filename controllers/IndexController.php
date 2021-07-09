<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
         * API No. 4
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "createUser":
            http_response_code(200);

            // Packet의 Body에서 데이터를 파싱합니다.
            $id = $req->id;
            $pwd =$req->pwd;
            $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT); // Password Hash
            $membership = $req->membership;

            if(!checkInput($id,$pwd,$membership)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "입력사항을 모두 기입해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isValidId($id)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "중복된 id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #멤버십
            if($membership!='B' and $membership!='S' and $membership!='P' ){
                $res->isSuccess = FALSE;
                $res->code = 103;
                $res->message = "유효하지 않은 멤버십입니다. (B:베이직 ,S:스탠다드 ,P:프리미엄)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            #패스워드 유효성 검사
            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/',$pwd) ) {
                $res->isSuccess = FALSE;
                $res->code = 105;
                $res->message = "비밀번호는 문자,숫자,특수문자 하나이상 8자이상-16자 이하입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            createUser($id, $pwd_hash, $membership);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

//        case "getUsers":
//            http_response_code(200);
//            $keyword = $_GET['keyword'];
//
//            if(!isExistId($keyword)){
//                $res->isSuccess = FALSE;
//                $res->code = 101;
//                $res->message = "검색된 유저가 없습니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            $res->result = getUsers($keyword);
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "회원 조회 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
//        /*
//         * API No. 5
//         * API Name : 테스트 Path Variable API
//         * 마지막 수정 날짜 : 19.04.29
//         */
//        case "getUserDetail":
//            http_response_code(200);
//
//            $idx = $vars["idx"];
//
//            if(!isValidUserIdx($idx)){
//                $res->isSuccess = FALSE;
//                $res->code = 101;
//                $res->message = "유효하지 않은 회원 idx입니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            $res->result = getUserDetail($vars["idx"]);
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "해당 회원 조회 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;

        case "patchUser":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $id = $req->id;
            $pwd =$req->pwd;
            $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT); // Password Hash
            $status = $req->status;
            $membership = $req->membership;

            if(!isValidUserIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 회원 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isValidId($id)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "중복된 id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #멤버십 체크
            if(!empty($membership) and  $membership!='B' and $membership!='S' and $membership!='P' ){
                $res->isSuccess = FALSE;
                $res->code = 103;
                $res->message = "유효하지 않은 멤버십입니다. (B:베이직 ,S:스탠다드 ,P:프리미엄)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #상태 체크
            if(!empty($status) and $status!='U' and $status!='D' and $status!='S' and $status!='W' ){
                $res->isSuccess = FALSE;
                $res->code = 104;
                $res->message = "유효하지 않은 상태입니다. (U:이용 ,D:휴면 ,S:정지 ,W:탈퇴)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            #패스워드 유효성 검사
            if (!empty($pwd) and !preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/',$pwd) ) {
                $res->isSuccess = FALSE;
                $res->code = 105;
                $res->message = "비밀번호는 문자,숫자,특수문자 하나이상 8자이상-16자 이하입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            #케이스
            if(empty($id) and empty($pwd) and empty($status) and empty($membership)){
                $res->isSuccess = FALSE;
                $res->code = 106;
                $res->message = "수정값이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!empty($id) and empty($pwd) and empty($status) and empty($membership)){
                $case=1;
            }
            else if(empty($id) and !empty($pwd) and empty($status) and empty($membership)){
                $case=2;
            }
            else if(empty($id) and empty($pwd) and !empty($status) and empty($membership)){
                $case=3;
            }
            else if(empty($id) and empty($pwd) and empty($status) and !empty($membership)){
                $case=4;
            }
            else if(!empty($id) and !empty($pwd) and empty($status) and empty($membership)){
                $case=5;
            }
            else if(!empty($id) and empty($pwd) and !empty($status) and empty($membership)){
                $case=6;
            }
            else if(!empty($id) and empty($pwd) and empty($status) and !empty($membership)){
                $case=7;
            }
            else if(empty($id) and !empty($pwd) and !empty($status) and empty($membership)){
                $case=8;
            }
            else if(empty($id) and !empty($pwd) and empty($status) and !empty($membership)){
                $case=9;
            }
            else if(empty($id) and empty($pwd) and !empty($status) and !empty($membership)){
                $case=10;
            }
            else if(!empty($id) and !empty($pwd) and !empty($status) and empty($membership)){
                $case=11;
            }
            else if(!empty($id) and !empty($pwd) and empty($status) and !empty($membership)){
                $case=12;
            }
            else if(!empty($id) and empty($pwd) and !empty($status) and !empty($membership)){
                $case=13;
            }
            else if(empty($id) and !empty($pwd) and !empty($status) and !empty($membership)){
                $case=14;
            }
            else if(!empty($id) and !empty($pwd) and !empty($status) and !empty($membership)){
                $case=15;
            }


            patchUser($case,$id, $pwd_hash, $status,$membership,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "해당 회원정보 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteUser":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isDeleted($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "이미 삭제된 회원입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 회원 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            deleteUser($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "회원탈퇴 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 6
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */

        case "createProfile":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            $nickname = $req ->nickname;
            $icon =$req ->icon;
            $viewingLevel = $req ->viewingLevel;

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 회원 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            #이미 프로필이 5개 이상일경우
            if(isOver($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "프로필은 최대 5개까지 만들 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            #유저 내 중복된 프로필 닉네임이 있을경우
            if(isValidNickname($userIdx,$nickname)){
                $res->isSuccess = FALSE;
                $res->code = 103;
                $res->message = "유저의 프로필 중 중복된 닉네임이 존재합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            #입력값을 모두 기입하지 않았을 경우
            if(!checkInputProfile($nickname,$icon,$viewingLevel)){
                $res->isSuccess = FALSE;
                $res->code = 104;
                $res->message = "입력값을 모두 기입해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #닉네임에 특수문자 입력 불가
            if ( preg_match('/[^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}0-9a-zA-Z]/u',$nickname) ) {
                $res->isSuccess = FALSE;
                $res->code = 105;
                $res->message = "닉네임에 특수문자는 포함될 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #시청레벨 초이스
            if ($viewingLevel!='A' and $viewingLevel!='K'){
                $res->isSuccess = FALSE;
                $res->code = 106;
                $res->message = "올바른 시청허용등급을 입력해주세요(A:모든허용등급, K:키즈)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            createProfile($nickname,$icon,$viewingLevel,$userIdx);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "프로필 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getProfiles":
            http_response_code(200);


            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 회원 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getProfiles($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "프로필 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getProfileDetail":
            http_response_code(200);


            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $profileIdx = $vars["profileIdx"];

            if(!isValidProfileIdx($userIdx,$profileIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 프로필 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getProfileDetail($profileIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "해당 프로필 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "patchProfile":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            $profileIdx =$vars["profileIdx"];

            $nickname = $req ->nickname;
            $icon =$req ->icon;
            $viewingLevel =$req ->viewingLevel;

            $case =0;

            if(!isValidProfileIdx($userIdx,$profileIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 프로필 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #유저 내 중복된 프로필 닉네임이 있을경우
            if(isValidNickname($userIdx,$nickname)){
                $res->isSuccess = FALSE;
                $res->code = 103;
                $res->message = "유저의 프로필 중 중복된 닉네임이 존재합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #닉네임에 특수문자 입력 불가
            if ( preg_match('/[^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}0-9a-zA-Z]/u',$nickname) ) {
                $res->isSuccess = FALSE;
                $res->code = 105;
                $res->message = "닉네임에 특수문자는 포함될 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #시청레벨 초이스
            if (!empty($viewingLevel) and $viewingLevel!='A' and $viewingLevel!='K'){
                $res->isSuccess = FALSE;
                $res->code = 106;
                $res->message = "올바른 시청허용등급을 입력해주세요(A:모든허용등급, K:키즈)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #케이스
            if(!empty($nickname) and empty($icon) and empty($viewingLevel)){
                $case=1;
            }
            else if(empty($nickname) and !empty($icon) and empty($viewingLevel)){
                $case=2;
            }
            else if(empty($nickname) and empty($icon) and !empty($viewingLevel)){
                $case=3;
            }
            else if(empty($nickname) and !empty($icon) and !empty($viewingLevel)){
                $case=4;
            }
            else if(!empty($nickname) and empty($icon) and !empty($viewingLevel)){
                $case=5;
            }
            else if(!empty($nickname) and !empty($icon) and empty($viewingLevel)){
                $case=6;
            }
            else if(!empty($nickname) and !empty($icon) and !empty($viewingLevel)){
                $case=7;
            }
            else if(empty($nickname) and empty($icon) and empty($viewingLevel)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "수정된 값이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            patchProfile($case,$nickname,$icon,$viewingLevel,$profileIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "해당 프로필 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteProfile":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $profileIdx = $vars["profileIdx"];

            if(isDeletedProfile($profileIdx)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "이미 삭제된 프로필입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProfileIdx($userIdx,$profileIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 프로필 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            deleteProfile($profileIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "해당 프로필 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getVideos":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $profileIdx=$vars['profileIdx'];
            $keyword = $_GET['keyword'];

            if(empty($keyword)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "검색 키워드를 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isExistVideo($keyword)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "검색된 비디오가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getVideos($keyword,$profileIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "비디오 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getSeriesDetail":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $profileIdx = $vars["profileIdx"];
            $videogroupIdx = $vars["videogroupIdx"];
            $seasonNo=$vars["seasonNo"];

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 유저 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProfileIdx($userIdx,$profileIdx)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "유효하지 않은 프로필 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidVideo($videogroupIdx)){
                $res->isSuccess = FALSE;
                $res->code = 103;
                $res->message = "유효하지 않은 비디오그룹 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isSeries($videogroupIdx)=='N'){
                $res->isSuccess = FALSE;
                $res->code = 104;
                $res->message = "영화의 경우 시즌이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidSeason($videogroupIdx,$seasonNo)){
                $res->isSuccess = FALSE;
                $res->code = 105;
                $res->message = "유효하지 않은 시즌입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            if(isWatched($profileIdx,$videogroupIdx)){
                $res->result->detail = getSeriesWatched($profileIdx,$videogroupIdx);
            }
            else{
                $res->result ->detail= getSeriesUnwatched($profileIdx,$videogroupIdx);
            }

            $res->result->actor = getVideoActors($videogroupIdx);
            $res->result->producer = getVideoProducers($videogroupIdx);
            $res->result->writer = getVideoWriters($videogroupIdx);
            $res->result->episode = getVideoSeries($profileIdx,$videogroupIdx,$seasonNo);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "해당 비디오 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getMovieDetail":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $profileIdx = $vars["profileIdx"];
            $videogroupIdx = $vars["videogroupIdx"];

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 유저 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProfileIdx($userIdx,$profileIdx)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "유효하지 않은 프로필 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidVideo($videogroupIdx)){
                $res->isSuccess = FALSE;
                $res->code = 103;
                $res->message = "유효하지 않은 비디오그룹 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isSeries($videogroupIdx)=='Y'){
                $res->isSuccess = FALSE;
                $res->code = 104;
                $res->message = "시리즈물의 경우 시즌을 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isWatched($profileIdx,$videogroupIdx)){
                $res->result ->detail = getMovieWatched($profileIdx,$videogroupIdx);
            }
            else{
                $res->result ->detail = getMovieUnwatched($profileIdx,$videogroupIdx);
            }

            $res->result->actor = getVideoActors($videogroupIdx);
            $res->result->producer = getVideoProducers($videogroupIdx);
            $res->result->writer = getVideoWriters($videogroupIdx);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "해당 비디오 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getVideoInfo":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $videogroupIdx = $vars["videogroupIdx"];

            if(!isValidVideo($videogroupIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 비디오그룹 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result->title = getVideoTitle($videogroupIdx);
            $res->result->actor = getVideoActors($videogroupIdx);
            $res->result->producer = getVideoProducers($videogroupIdx);
            $res->result->writer = getVideoWriters($videogroupIdx);
            $res->result->viewingGrade = getVideoViewingGrade($videogroupIdx);
            $res->result -> risk = getVideoRisks($videogroupIdx);

            $res->result ->genre = getVideoGenres($videogroupIdx);
            $res->result -> characteristic= getVideoChars($videogroupIdx);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "해당 비디오 정보 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getHomePage":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $profileIdx = $vars["profileIdx"];


            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 유저 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProfileIdx($userIdx,$profileIdx)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "유효하지 않은 프로필 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            updateUserTendency($profileIdx); #유저경향 업데이트

            $res->result-> main -> detail = getRcmdVideo($profileIdx);

            $videogroupIdx = getRcmdVideo($profileIdx)[0]['profileIdx'];

            $res->result-> main->genre = getVideoGenres($videogroupIdx);
            $res->result -> main->characteristic= getVideoChars($videogroupIdx);

            $res->result-> hotList = getHotVideo($profileIdx);
            $res->result-> wishList = getWishlistVideo($profileIdx);
            $res->result-> watchingList = getWatchingVideo($profileIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "홈 화면 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createStatus":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $profileIdx = $vars["profileIdx"];
            $videogroupIdx=$vars["videogroupIdx"];
            $status = $req ->status;

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 101;
                $res->message = "유효하지 않은 유저 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProfileIdx($userIdx,$profileIdx)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "유효하지 않은 프로필 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            if(!isExistLike($profileIdx,$videogroupIdx)){
                $case =0;
                if($status!= 'D' and  $status!= 'L'){
                    $res->isSuccess = FALSE;
                    $res->code = 103;
                    $res->message = "평가값이 유효하지 않습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                createStatus($profileIdx,$videogroupIdx,$status);
            }
            else{
                switch (checkLikeStatus($profileIdx,$videogroupIdx)){
                    case 'L' : $case =1; break;
                    case 'D' : $case =1; break;
                    case 'N' : $case =2; break;
                }
                if($case==1 and $status!=null){
                    $res->isSuccess = FALSE;
                    $res->code = 105;
                    $res->message = "이미 평가가 되어있습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                if($case==2 and ($status!='L' and $status!= 'D')){
                    $res->isSuccess = FALSE;
                    $res->code = 103;
                    $res->message = "평가값이 유효하지 않습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                updateLike($case,$profileIdx,$videogroupIdx,$status);
            }

            if($case==1 and $status==null){
                $res->isSuccess = FALSE;
                $res->code = 104;
                $res->message = "평가 취소";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }



            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "평가 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createRecord":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 108;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 107;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            $profileIdx = $vars['profileIdx'];
            $videoIdx = $vars['videoIdx'];
            $elapsedTime = $req ->elapsedTime;

            if(!isValidProfileIdx($userIdx,$profileIdx)){
            $res->isSuccess = false;
            $res->code = 101;
            $res->message = "유효하지 않은 프로필idx 입니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
            }


            if(!isExistRecord($profileIdx,$videoIdx)){
                $case=1;
            }
            else{
                $case=2;
            }

            if(!isValidVideoIdx($videoIdx)){
                $res->isSuccess = false;
                $res->code = 102;
                $res->message = "유효하지 않은 비디오idx 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(empty($elapsedTime)){
                $res->isSuccess = false;
                $res->code = 103;
                $res->message = "경과시간의 입력값이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!is_int($elapsedTime)){
                $res->isSuccess = false;
                $res->code = 104;
                $res->message = "경과시간을 숫자로 입력해주세요(ex)1시간 20분 10초 -> 12010)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $h = sprintf('%d', $elapsedTime / 10000);
            $m = sprintf('%d', ($elapsedTime%10000) / 100);
            $s = $elapsedTime%100;
            $elapsedTimeSecond=$h*3600 + $m*60 + $s;

            if($h>838 or $m>59 or $s>59){
                $res->isSuccess = false;
                $res->code = 106;
                $res->message = "분,초는 60을 넘을수 없습니다. 최대시간은 8385959(838시간 59분 59초)입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($elapsedTimeSecond>checkRunningTime($videoIdx)){
                $res->isSuccess = false;
                $res->code = 105;
                $res->message = "경과시간은 영상시간보다 길 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            createRecord($case,$profileIdx,$videoIdx,$elapsedTime);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "시청기록 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "login":
            http_response_code(200);
            $jwt = $req->JWT;

            // 1) JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "인증실패";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 2) JWT Payload 반환

            $res->result = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "로그인 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        case "getBoards":
            http_response_code(200);


            $res->result = getBoards();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시물 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createBoard":
            http_response_code(200);




            $title = $_POST['title'];
            $tag = $_POST['tag'];
            $content = $_POST['content'];


            createBoard($title,$tag,$content);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시물 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteBoard":
            http_response_code(200);




            $boardIdx = $vars["boardIdx"];


            deleteBoard($boardIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시물 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;










    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
