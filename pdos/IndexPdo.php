<?php

//READ
function createUser($id, $pwd, $membership)
{
    $pdo = pdoSqlConnect();

    try {
        // From this point and until the transaction is being committed every change to the database can be reverted
        $pdo->beginTransaction();

        //유저 데이터 생성
        $query = "INSERT INTO USER (id, pwd, membership) VALUES (?,?,?);";

        $st = $pdo->prepare($query);
        $st->execute([$id,$pwd,$membership]);

        //프로필 생성
        $query = "insert into PROFILE (nickname, icon, userIdx)
select c.nickname,c.icon,idx
from USER
inner join (select '홍길동' as nickname,'http://홍길동.png' as icon) as c
where id = '$id';";

        $st = $pdo->prepare($query);
        $st->execute();

        //프로필 경향 생성
        $query = "insert into TENDENCY (profileIdx,g3) select PROFILE.idx , 0
from PROFILE
inner join USER on USER.idx= PROFILE.userIdx
where id='$id' and status='U';";



        $st = $pdo->prepare($query);
        $st->execute();


        // Make the changes to the database permanent
        $pdo->commit();

    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes

        $pdo->rollback();
        $res->isSuccess = FALSE;
        $res->code = 1000;
        $res->message = "DB 저장 에러";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        throw $e;
    }



}

#아이디 중복 검사
function isValidId($id)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where id = ? and status='U') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function checkInput($id,$pwd,$membership)
{
    if($id==null or $pwd==null or $membership==null){
        return 0;
    }

    else return 1;

}


//READ
function getUsers($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "select * from USER where id like concat('%',?,'%');";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

# 검색키워드 아이디 존재여부
function isExistId($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where id like concat('%',?,'%')) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


//READ
function getUserDetail($idx)
{
    $pdo = pdoSqlConnect();
    $query = "select * from USER where idx =?;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

//READ
function isValidUserIdx($idx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where idx = ? and status!='W') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

//READ
function patchUser($case,$id, $pwd_hash, $status,$membership,$userIdx)
{
    $pdo = pdoSqlConnect();

    try {
        // From this point and until the transaction is being committed every change to the database can be reverted
        $pdo->beginTransaction();

        switch ($case){
            case 1 : $query ="update USER set id='$id' where idx=?;"; break;
            case 2 : $query ="update USER set pwd='$pwd_hash' where idx=?;"; break;
            case 3 : $query ="update USER set status='$status' where idx=?;"; break;
            case 4 : $query ="update USER set membership='$membership' where idx=?;"; break;
            case 5 : $query ="update USER set id='$id',pwd='$pwd_hash' where idx=?;"; break;
            case 6 : $query ="update USER set id='$id',status='$status' where idx=?;"; break;
            case 7 : $query ="update USER set id='$id',membership='$membership' where idx=?;"; break;
            case 8 : $query ="update USER set pwd='$pwd_hash',status='$status'  where idx=?;"; break;
            case 9 : $query ="update USER set pwd='$pwd_hash',membership='$membership' where idx=?;"; break;
            case 10 : $query ="update USER set status='$status',membership='$membership' where idx=?;"; break;
            case 11 : $query ="update USER set id='$id',pwd='$pwd_hash',status='$status' where idx=?;"; break;
            case 12 : $query ="update USER set id='$id',pwd='$pwd_hash',membership='$membership' where idx=?;"; break;
            case 13 : $query ="update USER set id='$id',status='$status',membership='$membership' where idx=?;"; break;
            case 14 : $query ="update USER set pwd='$pwd_hash',status='$status',membership='$membership' where idx=?;"; break;
            case 15 : $query ="update USER set id='$id',pwd='$pwd_hash',status='$status',membership='$membership' where idx=?;"; break;
        }

        $st =$pdo->prepare($query);
        $st->execute([$userIdx]);

        // Make the changes to the database permanent
        $pdo->commit();
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }


}

function deleteUser($idx)
{
    $pdo = pdoSqlConnect();

    try {
        // From this point and until the transaction is being committed every change to the database can be reverted
        $pdo->beginTransaction();

        $query ="update USER set status='W' where idx=?;";

        $st =$pdo->prepare($query);
        $st->execute([$idx]);

        // Make the changes to the database permanent
        $pdo->commit();
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }

}

#유저 삭제 여부
function isDeleted($idx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where idx=? and status='W') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


function createProfile($nickname,$icon,$viewingLevel,$userIdx)
{
    $pdo = pdoSqlConnect();

    try {
        // From this point and until the transaction is being committed every change to the database can be reverted
        $pdo->beginTransaction();

        $query ="insert into PROFILE (nickname,icon,viewingLevel,userIdx) value (?,?,?,?);";

        $st =$pdo->prepare($query);
        $st->execute([$nickname,$icon,$viewingLevel,$userIdx]);

        // Make the changes to the database permanent
        $pdo->commit();
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }



}

#프로필 개수 초과 여부 확인
function isOver($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select case when count(*)>4 then 1 else 0 end as isOver
from PROFILE where userIdx=? and isDeleted='N';";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['isOver'];
}

function isValidNickname($userIdx,$nickname)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from PROFILE where userIdx=? and nickname=? and isDeleted='N') as exist; ";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$nickname]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function checkInputProfile($nickname,$icon,$viewingLevel)
{
    if($nickname==null or $icon==null or $viewingLevel==null){
        return 0;
    }

    else return 1;
}


function getProfiles($userIdx)
{
    $pdo = pdoSqlConnect();
    $query ="select userIdx,idx as profileIdx,nickname,icon from PROFILE where userIdx=? and isDeleted='N';";

    $st =$pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function getProfileDetail($profileIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select idx as profileIdx,nickname,icon,viewingLevel from PROFILE where idx =?;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function isValidProfileIdx($userIdx,$profileIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from PROFILE where userIdx=? and idx=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$profileIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


function patchProfile($case,$nickname,$icon,$viewingLevel,$profileIdx)
{
    $pdo = pdoSqlConnect();

    try {
        // From this point and until the transaction is being committed every change to the database can be reverted
        $pdo->beginTransaction();

        switch ($case){
            case 1:  $query ="update PROFILE set nickname='$nickname' where idx=$profileIdx;"; break;
            case 2:  $query ="update PROFILE set icon='$icon' where idx=$profileIdx;"; break;
            case 3:  $query ="update PROFILE set viewingLevel='$viewingLevel' where idx=$profileIdx;"; break;
            case 4:  $query ="update PROFILE set icon='$icon' ,viewingLevel= '$viewingLevel' where idx=$profileIdx;"; break;
            case 5:  $query ="update PROFILE set nickname='$nickname' ,viewingLevel= '$viewingLevel' where idx=$profileIdx;"; break;
            case 6:  $query ="update PROFILE set nickname='$nickname' ,icon='$icon'  where idx=$profileIdx;"; break;
            case 7:  $query ="update PROFILE set nickname='$nickname' ,icon='$icon' ,viewingLevel= '$viewingLevel' where idx=$profileIdx;"; break;
        }

        $st = $pdo->prepare($query);
        $st->execute();
        //    $st->execute();

        // Make the changes to the database permanent
        $pdo->commit();
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }





}

function deleteProfile($profileIdx)
{
    $pdo = pdoSqlConnect();

    try {
        // From this point and until the transaction is being committed every change to the database can be reverted
        $pdo->beginTransaction();

        $query ="update PROFILE set isDeleted='Y' where idx=?";

        $st =$pdo->prepare($query);
        $st->execute([$profileIdx]);

        // Make the changes to the database permanent
        $pdo->commit();
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }



}

function isDeletedProfile($profileIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from PROFILE where idx=? and isDeleted='Y') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

//READ
function getVideos($keyword,$profileIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select koko.*,case when koko.series='Y' then ifnull(coco.season,1)
           when koko.series='N' then 0 end as recentWatchedSeason
from (select VIDEOGROUP.idx,VIDEOGROUP.thumbnailImg,netflixOriginal,series,
       case when datediff(max(airedDate),curdate()) >0 then 'Y' else 'N' end as newEpisode,
       case when EXISTS(select * from (select *
from VIDEOGROUP order by hotScore desc limit 5) as d where d.idx=VIDEOGROUP.idx) then 'Y' else 'N' end as top5
from VIDEOGROUP
inner join VIDEO on videogroupIdx= VIDEOGROUP.idx
where VIDEOGROUP.title
like concat('%',?,'%')
group by VIDEOGROUP.idx
order by VIDEOGROUP.hotScore desc) as koko
left join (select *
from (select updatedAt,videogroupIdx,season
from RECORD
inner join VIDEO on VIDEO.idx = RECORD.videoIdx
where RECORD.isDeleted='N' and profileIdx=?
order by updatedAt desc
limit 1000) as c
group by videogroupIdx) as coco on coco.videogroupIdx=koko.idx;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$keyword,$profileIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

# 검색키워드 아이디 존재여부
function isExistVideo($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from VIDEOGROUP where title like concat('%',?,'%')) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


function isWatched($profileIdx,$videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select *
from RECORD
inner join VIDEO on RECORD.videoIdx= VIDEO.idx
where profileIdx = ? and videogroupIdx = ? and RECORD.isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isSeries($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select series
from VIDEOGROUP
where VIDEOGROUP.idx =?;";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['series'];
}

function getSeriesWatched($profileIdx,$videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select PROFILE.idx as profileIdx, icon, VIDEOGROUP.idx as videogroupIdx, title , thumbnailVideo, viewingGrade, HD as hd, max(e.season) as seasonCount,
       case when exists(select * from WISHLIST W where W.profileIdx= PROFILE.idx and W.videogroupIdx= VIDEOGROUP.idx)
           then 'Y' else 'N' end as wishStatus,
       ifnull(LIKES.status,'N') as likeStatus,
       date_format(c.airedDate,'%Y') as airedYear,
       case when datediff(c.createdAt,current_timestamp)>31 then 'Y' else 'N' end as newStatus,
       case when EXISTS(select *
from RECORD
inner join VIDEO on RECORD.videoIdx= VIDEO.idx
where profileIdx = PROFILE.idx and videogroupIdx = VIDEOGROUP.idx and RECORD.isDeleted='N') then 'Y' else 'N' end as viewStatus,
       series,
       d.videoIdx,d.season,d.episode,d.name as episodeTitle,d.content as videoContent,d.length as runningTime,d.status as elapsedTime,d.남은시간 as remainingTime

from VIDEOGROUP
inner join PROFILE
left outer join LIKES on LIKES.profileIdx= PROFILE.idx and LIKES.videogroupIdx= VIDEOGROUP.idx
inner join VIDEO as e on e.videogroupIdx= VIDEOGROUP.idx
inner join (select * from VIDEO order by airedDate desc) as c on c.videogroupIdx=VIDEOGROUP.idx
inner join (select profileIdx,videoIdx,videogroupIdx,episode,season,name,content,status,length,
       case when timediff(length,status) > 10000 then time_format(timediff(length,status),'%l시간 %i분')
                                                else time_format(timediff(length,status),'%i분') end as 남은시간
from RECORD
inner join VIDEO on RECORD.videoIdx= VIDEO.idx
where RECORD.profileIdx =? and videogroupIdx = ?
order by updatedAt desc
limit 1) as d
where PROFILE.idx = ? and VIDEOGROUP.idx= ?;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videogroupIdx,$profileIdx,$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getMovieWatched($profileIdx,$videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select PROFILE.idx as profileIdx, icon, VIDEOGROUP.idx as videogroupIdx, title, thumbnailVideo, viewingGrade, HD as hd,
       case when exists(select * from WISHLIST W where W.profileIdx= PROFILE.idx and W.videogroupIdx= VIDEOGROUP.idx)
           then 'Y' else 'N' end as wishStatus,
       ifnull(LIKES.status,'N') as likeStatus,
       date_format(c.airedDate,'%Y') as airedYear,
       case when datediff(c.createdAt,current_timestamp)>31 then 'Y' else 'N' end as newStatus,
       VIDEOGROUP.content as videogroupContent,
       VIDEO.idx as videoIdx,
       case when EXISTS(select *
from RECORD
inner join VIDEO on RECORD.videoIdx= VIDEO.idx
where profileIdx = PROFILE.idx and videogroupIdx = VIDEOGROUP.idx and RECORD.isDeleted='N') then 'Y' else 'N' end as viewStatus,
       series as series,
       d.length as runningTime,d.status as elapsedTime,d.남은시간 as remainingTime

from VIDEOGROUP
inner join PROFILE
left outer join LIKES on LIKES.profileIdx= PROFILE.idx and LIKES.videogroupIdx= VIDEOGROUP.idx
inner join VIDEO on VIDEO.videogroupIdx= VIDEOGROUP.idx
inner join (select * from VIDEO order by airedDate desc) as c on c.videogroupIdx=VIDEOGROUP.idx
inner join (select status,length,
       case when timediff(length,status) > 10000 then time_format(timediff(length,status),'%l시간 %i분')
                                                else time_format(timediff(length,status),'%i분') end as 남은시간
from RECORD
inner join VIDEO on videoIdx= VIDEO.idx
where profileIdx=? and videogroupIdx=?) as d
where PROFILE.idx = ? and VIDEOGROUP.idx= ?;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videogroupIdx,$profileIdx,$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


function getSeriesUnwatched($profileIdx,$videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select PROFILE.idx as profileIdx, icon, VIDEOGROUP.idx as videogroupIdx, title, thumbnailVideo, viewingGrade, HD as hd, max(VIDEO.season) as seasonCount,
       case when exists(select * from WISHLIST W where W.profileIdx= PROFILE.idx and W.videogroupIdx= VIDEOGROUP.idx)
           then 'Y' else 'N' end as wishStatus,
       ifnull(LIKES.status,'N') as likeStatus,
       date_format(c.airedDate,'%Y') as airedYear,
       case when datediff(c.createdAt,current_timestamp)>31 then 'Y' else 'N' end as newStatus,
       VIDEOGROUP.content as videogroupContent,
       min(VIDEO.idx) as videoIdx,
       case when EXISTS(select *
from RECORD
inner join VIDEO on RECORD.videoIdx= VIDEO.idx
where profileIdx = PROFILE.idx and videogroupIdx = VIDEOGROUP.idx and RECORD.isDeleted='N') then 'Y' else 'N' end as viewStatus,
       series as series

from VIDEOGROUP
inner join PROFILE
inner join VIDEO on VIDEO.videogroupIdx= VIDEOGROUP.idx
left outer join LIKES on LIKES.profileIdx= PROFILE.idx and LIKES.videogroupIdx= VIDEOGROUP.idx
inner join (select * from VIDEO order by airedDate desc) as c on c.videogroupIdx=VIDEOGROUP.idx
where PROFILE.idx =? and VIDEOGROUP.idx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


function getMovieUnwatched($profileIdx,$videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select PROFILE.idx as profileIdx, icon, VIDEOGROUP.idx as videogroupIdx, title, thumbnailVideo, viewingGrade, HD as hd,
       case when exists(select * from WISHLIST W where W.profileIdx= PROFILE.idx and W.videogroupIdx= VIDEOGROUP.idx)
           then 'Y' else 'N' end as wishStatus,
       ifnull(LIKES.status,'N') as likeStatus,
       date_format(c.airedDate,'%Y') as airedYear,
       case when datediff(c.createdAt,current_timestamp)>31 then 'Y' else 'N' end as newStatus,
       VIDEOGROUP.content as videogroupContent,
       VIDEO.idx as videoIdx,
       case when EXISTS(select *
from RECORD
inner join VIDEO on RECORD.videoIdx= VIDEO.idx
where profileIdx = PROFILE.idx and videogroupIdx = VIDEOGROUP.idx and RECORD.isDeleted='N') then 'Y' else 'N' end as viewStatus,
       series as series

from VIDEOGROUP
inner join PROFILE
inner join VIDEO on VIDEO.videogroupIdx= VIDEOGROUP.idx
left outer join LIKES on LIKES.profileIdx= PROFILE.idx and LIKES.videogroupIdx= VIDEOGROUP.idx
inner join (select * from VIDEO order by airedDate desc) as c on c.videogroupIdx=VIDEOGROUP.idx
where PROFILE.idx =? and VIDEOGROUP.idx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function isValidVideo($videogroupIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from VIDEOGROUP where idx=?) as exist";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidSeason($videogroupIdx,$seasonNo){
    $pdo = pdoSqlConnect();
    $query = "select Exists(select *
from VIDEOGROUP
inner join VIDEO on VIDEO.videogroupIdx= VIDEOGROUP.idx
where videogroupIdx=? and season=?
group by VIDEOGROUP.idx ) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx,$seasonNo]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


function getVideoActors($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select MAKER.name as actor
from MAKER
inner join MAKER_RLTNS MR on MR.makerIdx=MAKER.idx and MR.videogroupIdx
inner join VIDEOGROUP VG on VG.idx = MR.videogroupIdx
where classification='A' and videogroupIdx =?
order by priority;";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getVideoProducers($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select MAKER.name as producer
from MAKER
inner join MAKER_RLTNS MR on MR.makerIdx=MAKER.idx and MR.videogroupIdx
inner join VIDEOGROUP VG on VG.idx = MR.videogroupIdx
where classification='P' and videogroupIdx =?
order by priority;";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getVideoWriters($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select MAKER.name as writer
from MAKER
inner join MAKER_RLTNS MR on MR.makerIdx=MAKER.idx and MR.videogroupIdx
inner join VIDEOGROUP VG on VG.idx = MR.videogroupIdx
where classification='W' and videogroupIdx =?
order by priority;";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}



function getVideoSeries($profileIdx,$videogroupIdx,$season)
{
    $pdo = pdoSqlConnect();
    $query = "select idx as videoIdx,season,episode,name as episodeTitle,thumbnailImg,content as videoContent,
       ifnull(status,time(0)) as elapsedTime,
       length as runningTime
from VIDEO
left join (select *
from RECORD where profileIdx=? and isDeleted='N') R on R.videoIdx= VIDEO.idx
where videogroupIdx=? and season=?;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videogroupIdx,$season]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

#시즌 있는지 체크 함수
function checkSeason($videogroupIdx,$season)
{
    $pdo = pdoSqlConnect();
    $query = "select Exists(select * from VIDEO where videogroupIdx=? and season=?) as exist ;";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx,$season]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getVideoTitle($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select title from VIDEOGROUP where idx=?";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getVideoViewingGrade($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select viewingGrade from VIDEOGROUP where idx=?";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getVideoRisks($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select name , degree
from RISK
inner join RISK_RLTNS on RISK_RLTNS.riskIdx = RISK.idx
where videogroupIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getVideoGenres($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select name
from GENRE
inner join GENRE_RLTNS on GENRE_RLTNS.genreIdx= GENRE.idx
where videogroupIdx=? and (classification='G' or classification='B');";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getVideoChars($videogroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select name
from GENRE
inner join GENRE_RLTNS on GENRE_RLTNS.genreIdx= GENRE.idx
where videogroupIdx=? and classification='C' ;";

    $st = $pdo->prepare($query);
    $st->execute([$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function updateUserTendency($profileIdx)
{
    $pdo = pdoSqlConnect();

    try {
        $query = "# 좋아요 경향
    set @weight=1;
    UPDATE TENDENCY,
    (select COUNT(case when genreIdx=3 then 1 end) as 3t,
        COUNT(case when genreIdx=4 then 1 end) as 4t,
        COUNT(case when genreIdx=5 then 1 end) as 5t,
        COUNT(case when genreIdx=6 then 1 end) as 6t,
        COUNT(case when genreIdx=7 then 1 end) as 7t,
        COUNT(case when genreIdx=8 then 1 end) as 8t,
        COUNT(case when genreIdx=9 then 1 end) as 9t,
        COUNT(case when genreIdx=10 then 1 end) as 10t,
        COUNT(case when genreIdx=11 then 1 end) as 11t,
        COUNT(case when genreIdx=12 then 1 end) as 12t,
        COUNT(case when genreIdx=13 then 1 end) as 13t,
        COUNT(case when genreIdx=14 then 1 end) as 14t,
        COUNT(case when genreIdx=15 then 1 end) as 15t,
        COUNT(case when genreIdx=16 then 1 end) as 16t,
        COUNT(case when genreIdx=17 then 1 end) as 17t
    FROM LIKES
    INNER JOIN GENRE_RLTNS ON GENRE_RLTNS.videogroupIdx= LIKES.videogroupIdx
    WHERE LIKES.status='L' and LIKES.profileIdx=$profileIdx) as c
    Set g3=@weight*3t,
    g4=@weight*4t,
    g5=@weight*5t,
    g6=@weight*6t,
    g7=@weight*7t,
    g8=@weight*8t,
    g9=@weight*9t,
    g10=@weight*10t,
    g11=@weight*11t,
    g12=@weight*12t,
    g13=@weight*13t,
    g14=@weight*14t,
    g15=@weight*15t,
    g16=@weight*16t,
    g17=@weight*17t
    where TENDENCY.profileIdx=$profileIdx;
    
    #싫어요 경향
    set @weight=-5;
    UPDATE TENDENCY,
    (select COUNT(case when genreIdx=3 then 1 end) as 3t,
        COUNT(case when genreIdx=4 then 1 end) as 4t,
        COUNT(case when genreIdx=5 then 1 end) as 5t,
        COUNT(case when genreIdx=6 then 1 end) as 6t,
        COUNT(case when genreIdx=7 then 1 end) as 7t,
        COUNT(case when genreIdx=8 then 1 end) as 8t,
        COUNT(case when genreIdx=9 then 1 end) as 9t,
        COUNT(case when genreIdx=10 then 1 end) as 10t,
        COUNT(case when genreIdx=11 then 1 end) as 11t,
        COUNT(case when genreIdx=12 then 1 end) as 12t,
        COUNT(case when genreIdx=13 then 1 end) as 13t,
        COUNT(case when genreIdx=14 then 1 end) as 14t,
        COUNT(case when genreIdx=15 then 1 end) as 15t,
        COUNT(case when genreIdx=16 then 1 end) as 16t,
        COUNT(case when genreIdx=17 then 1 end) as 17t
    FROM LIKES
    INNER JOIN GENRE_RLTNS ON GENRE_RLTNS.videogroupIdx= LIKES.videogroupIdx
    WHERE LIKES.status='D' and LIKES.profileIdx=$profileIdx) as c
    Set g3=g3 +@weight*3t,
    g4=g4 +@weight*4t,
    g5=g5 +@weight*5t,
    g6=g6 +@weight*6t,
    g7=g7 +@weight*7t,
    g8=g8 +@weight*8t,
    g9=g9 +@weight*9t,
    g10=g10 +@weight*10t,
    g11=g11 +@weight*11t,
    g12=g12 +@weight*12t,
    g13=g13 +@weight*13t,
    g14=g14 +@weight*14t,
    g15=g15 +@weight*15t,
    g16=g16 +@weight*16t,
    g17=g17 +@weight*17t
    where TENDENCY.profileIdx=$profileIdx;
    
    # 시청기록 경향
    set @weight =2;
    UPDATE TENDENCY,
    (select COUNT(case when genreIdx=3 then 1 end) as 3t,
        COUNT(case when genreIdx=4 then 1 end) as 4t,
        COUNT(case when genreIdx=5 then 1 end) as 5t,
        COUNT(case when genreIdx=6 then 1 end) as 6t,
        COUNT(case when genreIdx=7 then 1 end) as 7t,
        COUNT(case when genreIdx=8 then 1 end) as 8t,
        COUNT(case when genreIdx=9 then 1 end) as 9t,
        COUNT(case when genreIdx=10 then 1 end) as 10t,
        COUNT(case when genreIdx=11 then 1 end) as 11t,
        COUNT(case when genreIdx=12 then 1 end) as 12t,
        COUNT(case when genreIdx=13 then 1 end) as 13t,
        COUNT(case when genreIdx=14 then 1 end) as 14t,
        COUNT(case when genreIdx=15 then 1 end) as 15t,
        COUNT(case when genreIdx=16 then 1 end) as 16t,
        COUNT(case when genreIdx=17 then 1 end) as 17t
    from GENRE_RLTNS
    inner join (select distinct VIDEO.videogroupIdx
    from RECORD
    inner join VIDEO on VIDEO.idx = RECORD.videoIdx and RECORD.status>1000
    where profileIdx=$profileIdx) as a on GENRE_RLTNS.videogroupIdx = a.videogroupIdx) as c
    Set g3=g3 +@weight*3t,
    g4=g4 +@weight*4t,
    g5=g5 +@weight*5t,
    g6=g6 +@weight*6t,
    g7=g7 +@weight*7t,
    g8=g8 +@weight*8t,
    g9=g9 +@weight*9t,
    g10=g10 +@weight*10t,
    g11=g11 +@weight*11t,
    g12=g12 +@weight*12t,
    g13=g13 +@weight*13t,
    g14=g14 +@weight*14t,
    g15=g15 +@weight*15t,
    g16=g16 +@weight*16t,
    g17=g17 +@weight*17t
    where TENDENCY.profileIdx=$profileIdx;
    
    # 선호 총 경향점수
    UPDATE TENDENCY,
    (select case when g3>0 then g3 else 0 end as p3,
    case when g4>0 then g4 else 0 end as p4,
    case when g5>0 then g5 else 0 end as p5,
    case when g6>0 then g6 else 0 end as p6,
    case when g7>0 then g7 else 0 end as p7,
    case when g8>0 then g8 else 0 end as p8,
    case when g9>0 then g9 else 0 end as p9,
    case when g10>0 then g10 else 0 end as p10,
    case when g11>0 then g11 else 0 end as p11,
    case when g12>0 then g12 else 0 end as p12,
    case when g13>0 then g13 else 0 end as p13,
    case when g14>0 then g14 else 0 end as p14,
    case when g15>0 then g15 else 0 end as p15,
    case when g16>0 then g16 else 0 end as p16,
    case when g17>0 then g17 else 0 end as p17
    from TENDENCY where profileIdx=$profileIdx) as c
    set totalPositive= p3+p4+p5+p6+p7+p8+p9+p10+p11+p12+p13+p14+p15+p16+p17
    where profileIdx=$profileIdx;
    
    # 비선호 총 경향점수
    UPDATE TENDENCY,
    (select case when g3<=0 then g3 else 0 end as p3,
    case when g4<=0 then g4 else 0 end as p4,
    case when g5<=0 then g5 else 0 end as p5,
    case when g6<=0 then g6 else 0 end as p6,
    case when g7<=0 then g7 else 0 end as p7,
    case when g8<=0 then g8 else 0 end as p8,
    case when g9<=0 then g9 else 0 end as p9,
    case when g10<=0 then g10 else 0 end as p10,
    case when g11<=0 then g11 else 0 end as p11,
    case when g12<=0 then g12 else 0 end as p12,
    case when g13<=0 then g13 else 0 end as p13,
    case when g14<=0 then g14 else 0 end as p14,
    case when g15<=0 then g15 else 0 end as p15,
    case when g16<=0 then g16 else 0 end as p16,
    case when g17<=0 then g17 else 0 end as p17
    from TENDENCY where profileIdx=$profileIdx) as c
    set totalNegative=p3+p4+p5+p6+p7+p8+p9+p10+p11+p12+p13+p14+p15+p16+p17
    where profileIdx=$profileIdx;";

        $st = $pdo->prepare($query);
        $st->execute();
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }





}

function getRcmdVideo($profileIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select koko.*,ifnull(coco.status,time(0)) as elapsedTime,
       case when koko.series='Y' then ifnull(coco.season,1)
           when koko.series='N' then 0 end as recentWatchedSeason
from (select PROFILE.idx as profileIdx,icon,VIDEOGROUP.idx as videogroupIdx,VIDEOGROUP.thumbnailImg,nameImg,series,netflixOriginal,
       case when EXISTS(select * from WISHLIST where WISHLIST.profileIdx=PROFILE.idx) then 'Y' else 'N' end as wishStatus,
       min(VIDEO.idx) as videoIdx
from VIDEOGROUP
inner join (select d.idx,CAST(50+50*(r3+r4+r5+r6+r7+r8+r9+r10+r11+r12+r13+r14+r15+r16+r17) AS signed integer)  as matchRate,d.hotScore
from (select c.idx,c.hotScore,
       case when g3>0 then g3*3t/totalPositive when g3=0 then 0 else  -g3*3t/TENDENCY.totalNegative end as r3,
       case when g4>0 then g4*4t/totalPositive when g4=0 then 0 else  -g4*4t/TENDENCY.totalNegative end as r4,
       case when g5>0 then g5*5t/totalPositive when g5=0 then 0  else  -g5*5t/TENDENCY.totalNegative end as r5,
       case when g6>0 then g6*6t/totalPositive when g6=0 then 0  else  -g6*6t/TENDENCY.totalNegative end as r6,
       case when g7>0 then g7*7t/totalPositive when g7=0 then 0  else  -g7*7t/TENDENCY.totalNegative end as r7,
       case when g8>0 then g8*8t/totalPositive when g8=0 then 0  else  -g8*8t/TENDENCY.totalNegative end as r8,
       case when g9>0 then g9*9t/totalPositive when g9=0 then 0  else  -g9*9t/TENDENCY.totalNegative end as r9,
       case when g10>0 then g10*10t/totalPositive when g10=0 then 0  else  -g10*10t/TENDENCY.totalNegative end as r10,
       case when g11>0 then g11*11t/totalPositive when g11=0 then 0  else  -g11*11t/TENDENCY.totalNegative end as r11,
       case when g12>0 then g12*12t/totalPositive when g12=0 then 0  else  -g12*12t/TENDENCY.totalNegative end as r12,
       case when g13>0 then g13*13t/totalPositive when g13=0 then 0  else  -g13*13t/TENDENCY.totalNegative end as r13,
       case when g14>0 then g14*14t/totalPositive when g14=0 then 0  else  -g14*14t/TENDENCY.totalNegative end as r14,
       case when g15>0 then g15*15t/totalPositive when g15=0 then 0  else  -g15*15t/TENDENCY.totalNegative end as r15,
       case when g16>0 then g16*16t/totalPositive when g16=0 then 0  else  -g16*16t/TENDENCY.totalNegative end as r16,
       case when g17>0 then g17*17t/totalPositive when g17=0 then 0  else  -g17*17t/TENDENCY.totalNegative end as r17
from TENDENCY
inner join (select b.idx,hotScore,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=3 and videogroupIdx=b.idx) then 1 else 0 end as 3t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=4 and videogroupIdx=b.idx) then 1 else 0 end as 4t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=5 and videogroupIdx=b.idx) then 1 else 0 end as 5t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=6 and videogroupIdx=b.idx) then 1 else 0 end as 6t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=7 and videogroupIdx=b.idx) then 1 else 0 end as 7t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=8 and videogroupIdx=b.idx) then 1 else 0 end as 8t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=9 and videogroupIdx=b.idx) then 1 else 0 end as 9t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=10 and videogroupIdx=b.idx) then 1 else 0 end as 10t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=11 and videogroupIdx=b.idx) then 1 else 0 end as 11t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=12 and videogroupIdx=b.idx) then 1 else 0 end as 12t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=13 and videogroupIdx=b.idx) then 1 else 0 end as 13t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=14 and videogroupIdx=b.idx) then 1 else 0 end as 14t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=15 and videogroupIdx=b.idx) then 1 else 0 end as 15t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=16 and videogroupIdx=b.idx) then 1 else 0 end as 16t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=17 and videogroupIdx=b.idx) then 1 else 0 end as 17t
from (select VIDEOGROUP.idx,1*좋아요 -5*싫어요 +2*시청수 as hotScore
from VIDEOGROUP
inner join (select count(videogroupIdx) as 좋아요,VIDEOGROUP.idx
from VIDEOGROUP
left join LIKES L on L.videogroupIdx = idx and L.status='L'
group by idx) as c1 on c1.idx = VIDEOGROUP.idx
inner join (select count(videogroupIdx) as 싫어요,VIDEOGROUP.idx
from VIDEOGROUP
left join LIKES L on L.videogroupIdx = idx and L.status='D'
group by idx) as c2 on c2.idx = VIDEOGROUP.idx
inner join (select count(VIDEOGROUP.idx) as 시청수,VIDEOGROUP.idx
from VIDEOGROUP
inner join VIDEO on VIDEO.videogroupIdx=VIDEOGROUP.idx
left join RECORD R on R.videoIdx= VIDEO.idx
group by VIDEOGROUP.idx) as c3 on c3.idx =VIDEOGROUP.idx
order by hotScore desc
limit 7) as b ) as c
where profileIdx=$profileIdx) as d order by matchRate desc limit 1) as a on a.idx = VIDEOGROUP.idx
inner join PROFILE
inner join VIDEO on VIDEO.videogroupIdx= VIDEOGROUP.idx
where PROFILE.idx=$profileIdx) as koko
left join (select RECORD.updatedAt,status,videogroupIdx,profileIdx,season,videoIdx
from RECORD
inner join VIDEO on VIDEO.idx =RECORD.videoIdx where RECORD.isDeleted='N') as coco on coco.videogroupIdx=koko.videogroupIdx and coco.profileIdx=koko.profileIdx
order by updatedAt desc
limit 1;";

    $st = $pdo->prepare($query);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}



function getHotVideo($profileIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select koko.*,
       case when koko.series='Y' then ifnull(coco.season,1)
           when koko.series='N' then 0 end as recentWatchedSeason
from (select a.idx as videogroupIdx,a.thumbnailImg,netflixOriginal,
       case when datediff(max(airedDate),curdate()) >0 then 'Y' else 'N' end as newEpisode,
       case when EXISTS(select * from (select *
from VIDEOGROUP order by hotScore desc limit 5) as d where d.idx=a.idx) then 'Y' else 'N' end as top5,a.series
from (select a.idx, title, content,thumbnailImg, thumbnailVideo, viewingGrade, nameImg, isDeleted, createdAt, series, netflixOriginal, HD, a.matchRate, a.hotScore
from VIDEOGROUP
inner join (select d.idx,CAST(50+50*(r3+r4+r5+r6+r7+r8+r9+r10+r11+r12+r13+r14+r15+r16+r17) AS signed integer)  as matchRate,d.hotScore
from (select c.idx,c.hotScore,
       case when g3>0 then g3*3t/totalPositive when g3=0 then 0 else  -g3*3t/TENDENCY.totalNegative end as r3,
       case when g4>0 then g4*4t/totalPositive when g4=0 then 0 else  -g4*4t/TENDENCY.totalNegative end as r4,
       case when g5>0 then g5*5t/totalPositive when g5=0 then 0  else  -g5*5t/TENDENCY.totalNegative end as r5,
       case when g6>0 then g6*6t/totalPositive when g6=0 then 0  else  -g6*6t/TENDENCY.totalNegative end as r6,
       case when g7>0 then g7*7t/totalPositive when g7=0 then 0  else  -g7*7t/TENDENCY.totalNegative end as r7,
       case when g8>0 then g8*8t/totalPositive when g8=0 then 0  else  -g8*8t/TENDENCY.totalNegative end as r8,
       case when g9>0 then g9*9t/totalPositive when g9=0 then 0  else  -g9*9t/TENDENCY.totalNegative end as r9,
       case when g10>0 then g10*10t/totalPositive when g10=0 then 0  else  -g10*10t/TENDENCY.totalNegative end as r10,
       case when g11>0 then g11*11t/totalPositive when g11=0 then 0  else  -g11*11t/TENDENCY.totalNegative end as r11,
       case when g12>0 then g12*12t/totalPositive when g12=0 then 0  else  -g12*12t/TENDENCY.totalNegative end as r12,
       case when g13>0 then g13*13t/totalPositive when g13=0 then 0  else  -g13*13t/TENDENCY.totalNegative end as r13,
       case when g14>0 then g14*14t/totalPositive when g14=0 then 0  else  -g14*14t/TENDENCY.totalNegative end as r14,
       case when g15>0 then g15*15t/totalPositive when g15=0 then 0  else  -g15*15t/TENDENCY.totalNegative end as r15,
       case when g16>0 then g16*16t/totalPositive when g16=0 then 0  else  -g16*16t/TENDENCY.totalNegative end as r16,
       case when g17>0 then g17*17t/totalPositive when g17=0 then 0  else  -g17*17t/TENDENCY.totalNegative end as r17
from TENDENCY
inner join (select b.idx,hotScore,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=3 and videogroupIdx=b.idx) then 1 else 0 end as 3t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=4 and videogroupIdx=b.idx) then 1 else 0 end as 4t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=5 and videogroupIdx=b.idx) then 1 else 0 end as 5t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=6 and videogroupIdx=b.idx) then 1 else 0 end as 6t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=7 and videogroupIdx=b.idx) then 1 else 0 end as 7t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=8 and videogroupIdx=b.idx) then 1 else 0 end as 8t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=9 and videogroupIdx=b.idx) then 1 else 0 end as 9t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=10 and videogroupIdx=b.idx) then 1 else 0 end as 10t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=11 and videogroupIdx=b.idx) then 1 else 0 end as 11t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=12 and videogroupIdx=b.idx) then 1 else 0 end as 12t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=13 and videogroupIdx=b.idx) then 1 else 0 end as 13t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=14 and videogroupIdx=b.idx) then 1 else 0 end as 14t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=15 and videogroupIdx=b.idx) then 1 else 0 end as 15t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=16 and videogroupIdx=b.idx) then 1 else 0 end as 16t,
       case when EXISTS(select * from GENRE_RLTNS where genreIdx=17 and videogroupIdx=b.idx) then 1 else 0 end as 17t
from (select VIDEOGROUP.idx,hotScore
from VIDEOGROUP
order by hotScore desc
limit 7) as b ) as c
where profileIdx=?) as d) as a on a.idx =VIDEOGROUP.idx) as a
inner join VIDEO on VIDEO.videogroupIdx= a.idx
group by a.idx
order by a.matchRate desc) as koko
left join (select *
from (select updatedAt,videogroupIdx,season
from RECORD
inner join VIDEO on VIDEO.idx = RECORD.videoIdx
where RECORD.isDeleted='N' and profileIdx=?
order by updatedAt desc
limit 1000) as c
group by videogroupIdx) as coco on coco.videogroupIdx=koko.videogroupIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$profileIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getWishlistVideo($profileIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select koko.*,
       case when koko.series='Y' then ifnull(coco.season,1)
           when koko.series='N' then 0 end as recentWatchedSeason
from (select VIDEOGROUP.idx as videogroupIdx,VIDEOGROUP.thumbnailImg,netflixOriginal,series,
       case when datediff(new_epi,curdate()) >0 then 'Y' else 'N' end as newEpisode,
       case when EXISTS(select * from (select *
from VIDEOGROUP order by hotScore desc limit 5) as d where d.idx=videogroupIdx) then 'Y' else 'N' end as top5
from WISHLIST
inner join VIDEOGROUP on WISHLIST.videogroupIdx=VIDEOGROUP.idx
inner join (select VIDEO.videogroupIdx as idx ,max(airedDate) as new_epi
from VIDEO
join WISHLIST on WISHLIST.videogroupIdx = VIDEO.videogroupIdx
where WISHLIST.profileIdx=?
group by VIDEO.videogroupIdx) as c on c.idx = VIDEOGROUP.idx
where profileIdx=?) as koko
left join (select *
from (select updatedAt,videogroupIdx,season
from RECORD
inner join VIDEO on VIDEO.idx = RECORD.videoIdx
where RECORD.isDeleted='N' and profileIdx=?
order by updatedAt desc
limit 1000) as c
group by videogroupIdx) as coco on coco.videogroupIdx=koko.videogroupIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$profileIdx,$profileIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getWatchingVideo($profileIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select  c.*,series,VIDEOGROUP.thumbnailImg,netflixOriginal,
       case when datediff(max(VIDEO.airedDate),curdate()) >0 then 'Y' else 'N' end as newEpisode,
       case when EXISTS(select * from (select *
from VIDEOGROUP order by hotScore desc limit 5) as d where d.idx=c.videogroupIdx) then 'Y' else 'N' end as top5

from (select updatedAt,videoIdx,videogroupIdx,ifnull(season,0) as recentWatchedSeason,RECORD.status as elapsedTime,ifnull(episode,0) as episode,length as runningTime
from RECORD
inner join VIDEO on VIDEO.idx = RECORD.videoIdx
where RECORD.isDeleted='N' and profileIdx=?
order by updatedAt desc
limit 1000) as c

inner join VIDEOGROUP on c.videogroupIdx=VIDEOGROUP.idx
inner join VIDEO on c.videoIdx=VIDEO.idx
group by videogroupIdx
;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}



//비디오 평가하기
function createStatus($profileIdx,$videogroupIdx,$status){
    $pdo = pdoSqlConnect();

    try {
        // From this point and until the transaction is being committed every change to the database can be reverted
        $pdo->beginTransaction();

        $query = "insert into LIKES (profileIdx,videogroupIdx,status) values (?,?,?);";

        $st = $pdo->prepare($query);
        $st->execute([$profileIdx,$videogroupIdx,$status]);
        //    $st->execute();

        // Make the changes to the database permanent
        $pdo->commit();
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }

}

#LIKE 존재여부 체크
function isExistLike($profileIdx,$videogroupIdx){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select *
from LIKES where profileIdx=? and videogroupIdx=?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function updateLike($case,$profileIdx,$videogroupIdx,$status){
    $pdo = pdoSqlConnect();

    try {
        // From this point and until the transaction is being committed every change to the database can be reverted
        $pdo->beginTransaction();

        switch ($case){
            case 1 : $query = "update LIKES set status= 'N' where profileIdx=? and videogroupIdx=? ;"; break;
            case 2 : $query = "update LIKES set status= '$status' where profileIdx=? and videogroupIdx=? ;"; break;
        }
        $st = $pdo->prepare($query);
        $st->execute([$profileIdx,$videogroupIdx]);
        //    $st->execute();

        // Make the changes to the database permanent
        $pdo->commit();
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }
}

function checkLikeStatus($profileIdx,$videogroupIdx){
    $pdo = pdoSqlConnect();
    $query = "select status
from LIKES where profileIdx=? and videogroupIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videogroupIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['status'];
}

function createRecord($case,$profileIdx,$videoIdx,$elapsedTime){
    $pdo = pdoSqlConnect();
    switch ($case){
        case 1 : $query ="insert into RECORD (videoIdx, profileIdx, status) values (?,?,$elapsedTime);"; break;
        case 2 : $query ="update RECORD set status=$elapsedTime where videoIdx=? and profileIdx=?;"; break;
    }
    $st = $pdo->prepare($query);
    $st->execute([$videoIdx,$profileIdx]);

}

function checkRunningTime($videoIdx){
    $pdo = pdoSqlConnect();
    $query ="select time_to_sec(length) as runningTime from VIDEO where idx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$videoIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['runningTime'];

}



function isExistRecord($profileIdx,$videoIdx){
    $pdo = pdoSqlConnect();
    $query ="select Exists(select * from RECORD where profileIdx=? and videoIdx=?) as exist;";
    $st = $pdo->prepare($query);
    $st->execute([$profileIdx,$videoIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidVideoIdx($videoIdx){
    $pdo = pdoSqlConnect();
    $query ="select Exists(select * from VIDEO where idx=?) as exist;";
    $st = $pdo->prepare($query);
    $st->execute([$videoIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}



function getBoards()
{
    $pdo = pdoSqlConnect();
    $query = "select * from BOARD where isDeleted='N';";

    $st = $pdo->prepare($query);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function createBoard($title,$tag,$content)
{
    $pdo = pdoSqlConnect();
    $query = "insert into BOARD (title, tag, content) values (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$title,$tag,$content]);
    //    $st->execute();


    $st = null;
    $pdo = null;

}

function deleteBoard($boardIdx)
{
    $pdo = pdoSqlConnect();
    $query = "update BOARD set BOARD.isDeleted= 'Y' where idx=?  ;";

    $st = $pdo->prepare($query);
    $st->execute([$boardIdx]);
    //    $st->execute();


    $st = null;
    $pdo = null;

}


// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
