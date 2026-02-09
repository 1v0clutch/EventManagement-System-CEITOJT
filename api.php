<?php
header('Content-Type: application/json');

$DATA_DIR = __DIR__ . '/data';
if(!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0777, true);
$EVENTS_FILE = $DATA_DIR . '/events.json';
$MEMBERS_FILE = $DATA_DIR . '/members.json';
$AVAIL_FILE = $DATA_DIR . '/availability.json';

function load_json($path, $assoc=true){
    if(!file_exists($path)) return $assoc ? [] : null;
    $text = @file_get_contents($path);
    if($text === false || trim($text) === '') return $assoc ? [] : null;
    $data = json_decode($text, $assoc);
    return $data === null ? [] : $data;
}

function save_json($path, $data){
    $json = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($path, $json, LOCK_EX);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? $_GET['action'] ?? null;

// helpers
function ensure_member($member){
    global $MEMBERS_FILE;
    $members = load_json($MEMBERS_FILE);
    if(!in_array($member, $members)){
        $members[] = $member;
        save_json($MEMBERS_FILE, $members);
    }
}

function minutes_of($time){
    // expects HH:MM
    $parts = explode(':', $time);
    return intval($parts[0])*60 + intval($parts[1]);
}

function member_is_busy_at($member, $date, $time){
    global $AVAIL_FILE;
    $avail = load_json($AVAIL_FILE);
    $eventMin = minutes_of($time);
    $entries = $avail[$member] ?? [];
    foreach($entries as $e){
        if($e['date'] !== $date) continue;
        $s = minutes_of($e['start']);
        $t = minutes_of($e['end']);
        if($s <= $eventMin && $eventMin < $t) return ['busy'=>true, 'reason'=>"Busy {$e['start']}-{$e['end']} on {$date}"];
    }
    return ['busy'=>false];
}

switch($action){
    case 'create_event':
        $title = trim($input['title'] ?? 'Untitled');
        $date = $input['date'] ?? null;
        $time = $input['time'] ?? null;
        $members = $input['members'] ?? [];
        if(!$date || !$time){ http_response_code(400); echo json_encode(['error'=>'date and time required']); exit; }
        $events = load_json($EVENTS_FILE);
        $id = uniqid('ev_', true);
        $event = ['id'=>$id, 'title'=>$title, 'date'=>$date, 'time'=>$time, 'members'=>$members, 'created_at'=>date('c')];
        $events[] = $event;
        save_json($EVENTS_FILE, $events);
        foreach($members as $m) ensure_member($m);
        // compute availability snapshot
        $availability = [];
        foreach($members as $m){
            $check = member_is_busy_at($m, $date, $time);
            $availability[$m] = $check['busy'] ? ['available'=>false,'busy_reason'=>$check['reason']] : ['available'=>true];
        }
        echo json_encode(['message'=>'Event created','event'=>$event,'availability'=>$availability]);
        break;

    case 'list_events':
        $events = load_json($EVENTS_FILE);
        // sort by date+time
        usort($events, function($a,$b){
            $ka = $a['date'].' '.$a['time'];
            $kb = $b['date'].' '.$b['time'];
            return strcmp($ka,$kb);
        });
        echo json_encode(['events'=>$events]);
        break;

    case 'set_availability':
        $member = trim($input['member'] ?? '');
        $date = $input['date'] ?? null;
        $start = $input['start'] ?? null;
        $end = $input['end'] ?? null;
        if(!$member || !$date || !$start || !$end){ http_response_code(400); echo json_encode(['error'=>'member,date,start,end required']); exit; }
        $avail = load_json($AVAIL_FILE);
        if(!isset($avail[$member])) $avail[$member] = [];
        $avail[$member][] = ['date'=>$date,'start'=>$start,'end'=>$end];
        save_json($AVAIL_FILE, $avail);
        ensure_member($member);
        echo json_encode(['message'=>'Availability saved for '.$member]);
        break;

    case 'get_member_availability_for_event':
        $event_id = $input['event_id'] ?? null;
        $member = $input['member'] ?? null;
        if(!$event_id || !$member){ http_response_code(400); echo json_encode(['error'=>'event_id and member required']); exit; }
        $events = load_json($EVENTS_FILE);
        $found = null;
        foreach($events as $e) if($e['id'] === $event_id) { $found = $e; break; }
        if(!$found){ http_response_code(404); echo json_encode(['error'=>'event not found']); exit; }
        $check = member_is_busy_at($member, $found['date'], $found['time']);
        if($check['busy']) echo json_encode(['available'=>false,'busy_reason'=>$check['reason']]);
        else echo json_encode(['available'=>true]);
        break;

    case 'list_members':
        $members = load_json($MEMBERS_FILE);
        echo json_encode(['members'=>$members]);
        break;

    default:
        echo json_encode(['error'=>'unknown action', 'received'=>$action]);
}

?>
