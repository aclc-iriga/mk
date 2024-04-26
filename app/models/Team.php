<?php

require_once 'App.php';

class Team extends App
{
    // table
    protected $table = 'teams';

    // properties
    protected $id;
    protected $number = 0;
    protected $name;
    protected $location;
    protected $age = 0;
    protected $height = '';
    protected $vital_stats = '';
    protected $meta = '';
    protected $avatar = 'candidate.png';
    protected $disabled = false;


    /***************************************************************************
     * Team constructor
     *
     * @param int $id
     */
    public function __construct($id = 0)
    {
        parent::__construct();

        // get other info
        if($id > 0) {
            $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $this->id          = $row['id'];
                $this->number      = $row['number'];
                $this->name        = $row['name'];
                $this->location    = $row['location'];
                $this->age         = intval($row['age']);
                $this->height      = $row['height'];
                $this->vital_stats = $row['vital_stats'];
                $this->avatar      = $row['avatar'];
                $this->meta        = $this->parseMetaData();
            }
        }
    }


    /***************************************************************************
     * Execute find
     *
     * @param $stmt
     * @return Team|false
     */
    private static function executeFind($stmt)
    {
        $stmt->execute();
        $result = $stmt->get_result();
        if($row = $result->fetch_assoc())
            return new Team($row['id']);
        else
            return false;
    }


    /***************************************************************************
     * Find team by id
     *
     * @param int $id
     * @return Team|boolean
     */
    public static function findById($id)
    {
        $team = new Team();
        $stmt = $team->conn->prepare("SELECT id FROM $team->table WHERE id = ?");
        $stmt->bind_param("i", $id);
        return self::executeFind($stmt);
    }


    /***************************************************************************
     * Convert team object to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'          => $this->id,
            'number'      => $this->number,
            'name'        => $this->name,
            'location'    => $this->location,
            'age'         => $this->age,
            'height'      => $this->height,
            'vital_stats' => $this->vital_stats,
            'meta'        => $this->meta,
            'avatar'      => $this->avatar,
            'disabled'    => $this->disabled
        ];
    }


    /***************************************************************************
     * Count all teams
     *
     * @return int
     */
    public static function count()
    {
        $team = new Team();
        $stmt = $team->conn->prepare("SELECT COUNT(id) as total_teams FROM $team->table");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_teams'];
    }


    /***************************************************************************
     * Get all teams as array of objects
     *
     * @param int $event_id
     * @return Team[]
     */
    public static function all($event_id = 0)
    {
        // gather team ids of eliminated teams
        $event = null;
        $eliminated_team_ids = [];
        if($event_id > 0) {
            require_once 'Event.php';
            $event = Event::findById($event_id);
            $eliminated_team_ids = $event->getRowEliminatedTeamIds();
        }

        // gather teams
        $team = new Team();
        $sql  = "SELECT id FROM $team->table ";
        if(sizeof($eliminated_team_ids) > 0) {
            $sql .= "WHERE id NOT IN (" . implode(', ', $eliminated_team_ids) . ") ";
        }
        $sql .= "ORDER BY number, id";
        $stmt = $team->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $teams = [];
        while($row = $result->fetch_assoc()) {
            $teams[] = new Team($row['id']);
        }

        // sort teams for an event
        if($event_id > 0) {
            require_once 'Arrangement.php';
            $sorted_teams   = [];
            $assigned_teams = [];
            $arrangements = Arrangement::all($event_id);

            for($i = 0; $i < sizeof($arrangements); $i++) {
                $arrangement = $arrangements[$i];

                $arranged_team = $arrangement->getTeam();
                $arranged_team_id = $arranged_team->getId();
                if(!in_array($arranged_team_id, $eliminated_team_ids)) {
                    $key = 'team_' . $arrangement->getOrder();
                    if(!isset($sorted_teams[$key]) && !in_array($arranged_team_id, $assigned_teams)) {
                        // push $arranged_team to $sorted_teams
                        $sorted_teams[$key] = $arranged_team;
                        $assigned_teams[] = $arranged_team_id;

                        // remove $arranged_team from $teams
                        for($j = 0; $j < sizeof($teams); $j++) {
                            if($teams[$j]->getId() == $arranged_team_id) {
                                unset($teams[$j]);
                                $teams = array_values($teams);
                                break;
                            }
                        }
                    }
                }
            }

            // merge $sorted_teams and remaining $teams
            $final_teams  = [];
            $total_orders = sizeof(Arrangement::orders());
            for($i = 1; $i <= $total_orders; $i++) {
                $key = 'team_' . $i;
                if(isset($sorted_teams[$key])) {
                    $sorted_teams[$key]->disabled = $sorted_teams[$key]->hasNotShownUpForEvent($event);
                    $final_teams[] = $sorted_teams[$key];
                }
                else if(isset($teams[0])) {
                    $teams[0]->disabled = $teams[0]->hasNotShownUpForEvent($event);
                    $final_teams[] = $teams[0];
                    unset($teams[0]);
                    $teams = array_values($teams);
                }
            }

            $teams = $final_teams;
        }

        return $teams;
    }


    /***************************************************************************
     * Get all teams as array of arrays
     *
     * @param int $event_id
     * @return array
     */
    public static function rows($event_id = 0)
    {
        $teams = [];
        foreach(self::all($event_id) as $team) {
            $teams[] = $team->toArray();
        }
        return $teams;
    }


    /***************************************************************************
     * Check if team id exists
     *
     * @param int $id
     * @return bool
     */
    public static function exists($id)
    {
        if(!$id)
            return false;

        return (self::findById($id) != false);
    }


    /***************************************************************************
     * Get the first Team record
     * @return Team|boolean
     */
    public static function first_record()
    {
        $first_team = false;
        $team = new Team();
        $stmt = $team->conn->prepare("SELECT `id` FROM $team->table ORDER BY `id` LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            $first_team = new Team($row['id']);
        }
        return $first_team;
    }


    /***************************************************************************
     * Insert team
     *
     * @return void
     */
    public function insert()
    {
        // check id
        if(self::exists($this->id))
            App::returnError('HTTP/1.1 409', 'Insert Error: team [id = ' . $this->id . '] already exists.');

        // proceed with insert
        $stmt = $this->conn->prepare("INSERT INTO $this->table(number, name, location, age, height, vital_stats, avatar) VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississs", $this->number, $this->name, $this->location, $this->age, $this->height, $this->vital_stats, $this->avatar);
        $stmt->execute();
        $this->id = $this->conn->insert_id;
    }


    /***************************************************************************
     * Update team
     *
     * @return void
     */
    public function update()
    {
        // check id
        if(!self::exists($this->id))
            App::returnError('HTTP/1.1 404', 'Update Error: team [id = ' . $this->id . '] does not exist.');

        // proceed with update
        $stmt = $this->conn->prepare("UPDATE $this->table SET number = ?, name = ?, location = ?, age = ?, height = ?, vital_stats = ?, avatar = ? WHERE id = ?");
        $stmt->bind_param("ississsi", $this->number, $this->name, $this->location, $this->age, $this->height, $this->vital_stats, $this->avatar, $this->id);
        $stmt->execute();
    }


    /***************************************************************************
     * Delete team
     *
     * @return void
     */
    public function delete()
    {
        // check id
        if(!self::exists($this->id))
            App::returnError('HTTP/1.1 404', 'Delete Error: team [id = ' . $this->id . '] does not exist.');

        // proceed with delete
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
    }


    /***************************************************************************
     * Set number
     *
     * @param int $number
     * @return void
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }


    /***************************************************************************
     * Set name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /***************************************************************************
     * Set location
     *
     * @param string $location
     * @return void
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }


    /***************************************************************************
     * Set age
     *
     * @param int $age
     * @return void
     */
    public function setAge($age)
    {
        $this->age = $age;
    }


    /***************************************************************************
     * Set height
     *
     * @param string $height
     * @return void
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }


    /***************************************************************************
     * Set vital stats
     *
     * @param string $vital_stats
     * @return void
     */
    public function setVitalStats($vital_stats)
    {
        $this->vital_stats = $vital_stats;
    }


    /***************************************************************************
     * Set avatar
     *
     * @param string $avatar
     * @return void
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }


    /***************************************************************************
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /***************************************************************************
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }


    /***************************************************************************
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /***************************************************************************
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }


    /***************************************************************************
     * Get age
     *
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }


    /***************************************************************************
     * Get height
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }


    /***************************************************************************
     * Get vital stats
     *
     * @return string
     */
    public function getVitalStats()
    {
        return $this->vital_stats;
    }


    /***************************************************************************
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }


    /***************************************************************************
     * Add team participant for an event
     *
     * @param Event $event
     * @param int $number
     * @param string $first_name
     * @param string $middle_name
     * @param string $last_name
     * @param string $gender
     * @param string $avatar
     * @return void
     */
    public function addParticipant($event, $number, $first_name, $middle_name, $last_name, $gender = 'male', $avatar = 'no-avatar.jpg')
    {
        require_once 'Participant.php';
        $participant = new Participant();
        $participant->setTeamId($this->id);
        $participant->setEventId($event->getId());
        $participant->setNumber($number);
        $participant->setFirstName($first_name);
        $participant->setMiddleName($middle_name);
        $participant->setLastName($last_name);
        $participant->setGender($gender);
        $participant->setAvatar($avatar);
        $participant->insert();
    }


    /***************************************************************************
     * Get all team participants for an event as array of objects
     *
     * @param Event $event
     * @return Participant[]
     */
    public function getAllParticipants($event)
    {
        require_once 'Participant.php';
        $participants = [];
        foreach(Participant::all($this->id, $event->getId()) as $participant) {
            $participants[] = $participant;
        }
        return $participants;
    }


    /***************************************************************************
     * Get all team participants for an event as array of arrays
     *
     * @param Event $event
     * @return Participant[]
     */
    public function getRowParticipants($event)
    {
        $participants = [];
        foreach(self::getAllParticipants($event) as $participant) {
            $participants[] = $participant->toArray();
        }
        return $participants;
    }


    /***************************************************************************
     * Get events for which the team never showed up, as array of objects
     *
     * @return Event[]
     */
    public function getAllNotShownEvents()
    {
        require_once 'Event.php';

        $table_noshows = (new Event())->getTableNoShows();
        $stmt = $this->conn->prepare("SELECT DISTINCT event_id FROM $table_noshows WHERE team_id = ? ORDER BY event_id");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();
        $events = [];
        while($row = $result->fetch_assoc()) {
            $events[] = new Event($row['event_id']);
        }

        return $events;
    }


    /***************************************************************************
     * Get events for which the team never showed up, as array of arrays
     *
     * @return array
     */
    public function getRowNotShownEvents()
    {
        $events = [];
        foreach($this->getAllNotShownEvents() as $event) {
            $events[] = $event->toArray();
        }

        return $events;
    }


    /***************************************************************************
     * Determine if the team never showed up for a given event
     *
     * @param Event $event
     * @return bool
     */
    public function hasNotShownUpForEvent($event)
    {
        return $event->hasTeamNotShownUp($this);
    }


    /***************************************************************************
     * Get events for which the team was eliminated, as array of objects
     *
     * @return Event[]
     */
    public function getAllEliminatedEvents()
    {
        require_once 'Event.php';

        $table_eliminations = (new Event())->getTableEliminations();
        $stmt = $this->conn->prepare("SELECT DISTINCT event_id FROM $table_eliminations WHERE team_id = ? ORDER BY event_id");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();
        $events = [];
        while($row = $result->fetch_assoc()) {
            $events[] = new Event($row['event_id']);
        }

        return $events;
    }


    /***************************************************************************
     * Get events for which the team was eliminated, as array of arrays
     *
     * @return array
     */
    public function getRowEliminatedEvents()
    {
        $events = [];
        foreach($this->getAllEliminatedEvents() as $event) {
            $events[] = $event->toArray();
        }

        return $events;
    }


    /***************************************************************************
     * Determine if the team is eliminated from a given event
     *
     * @param Event $event
     * @return bool
     */
    public function hasBeenEliminatedFromEvent($event)
    {
        return $event->hasTeamBeenEliminated($this);
    }


    /***************************************************************************
     * Parse meta data.
     *
     * @return string
     */
    public function parseMetaData()
    {
        $meta = '';
        $has_age         = $this->age > 0;
        $has_height      = trim($this->height) != '';
        $has_vital_stats = trim($this->vital_stats) != '';

        if($has_age || $has_height || $has_vital_stats) {
            if($has_age)
                $meta .= $this->age . ' yr' . ($this->age > 1 ? 's' : '') . '. old';
            if($has_age && ($has_height || $has_vital_stats))
                $meta .= ' |';
            if($has_height)
                $meta .= ' ' . $this->height;
            if($has_vital_stats)
                $meta .= ' (' . $this->vital_stats . ')';
        }
        return $meta;
    }


    /***************************************************************************
     * Determine if the team's location is in Camarines Sur
     * according to organizers
     *
     * @return bool
     */
    public function isFromCamSur()
    {
        $bool = false;
        $haystack = strtolower($this->location);
        $needles  = ['camarines sur', 'cam. sur', 'iriga city', 'naga city'];
        foreach($needles as $needle) {
            if(strpos($haystack, $needle) !== false) {
                $bool = true;
                break;
            }
        }
        return $bool;
    }
}
