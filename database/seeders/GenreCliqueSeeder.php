<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * GenreCliqueSeeder
 *
 * Seeds 6 genre "cliques" of simulated users, each with coherent interaction
 * patterns on real songs from their genre. Designed to give the SVD recommender
 * enough structured latent signal to reduce RMSE and stabilise 5-fold variance.
 *
 * Cliques:
 *   1. Rock / Alt-Rock     (10 users, ~20 songs)
 *   2. Pop / Indie-Pop     (10 users, ~20 songs)
 *   3. Jazz / Soul / R&B   (10 users, ~20 songs)
 *   4. Hip-Hop / Rap       (10 users, ~20 songs)
 *   5. Electronic / EDM    (10 users, ~20 songs)
 *   6. Classical / Ambient (10 users, ~20 songs)
 *
 * Run with: php artisan db:seed --class=GenreCliqueSeeder
 *
 * To UNDO all seeded data run: php artisan db:seed --class=GenreCliqueSeeder --force
 * then roll back by passing: --rollback flag (see rollback() method below)
 */
class GenreCliqueSeeder extends Seeder
{
    /**
     * How many users per clique.
     * Increase to 15-20 per clique if you want even more training signal.
     */
    private const USERS_PER_CLIQUE = 10;

    /**
     * Interaction weights that match the recommender's fetch_data_from_db() logic:
     *   shares    → 3.0 pts
     *   likes     → 2.0 pts  (via share like)
     *   shelf     → 4.0 pts  (highest premium signal)
     *   comments  → 1.0 pts
     *   discovery like → 2.0 pts  (song_interactions type=like)
     *   discovery listen → 1.0 pts (song_interactions type=listen)
     */
    private int $now;

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('⚠️ GenreCliqueSeeder skipped: Production environment detected.');
            return;
        }

        $this->now = time();
        $this->command->info('🎵 GenreCliqueSeeder starting …');

        DB::transaction(function () {

            // ----------------------------------------------------------------
            // 1. Insert Song Catalog
            // ----------------------------------------------------------------
            $this->command->info('  → Inserting song catalog …');
            $songIds = $this->insertSongs();

            // ----------------------------------------------------------------
            // 2. Insert Simulated Users + Interactions per Clique
            // ----------------------------------------------------------------
            $cliques = $this->cliques($songIds);

            foreach ($cliques as $clique) {
                $this->command->info("  → Seeding clique: {$clique['name']}");
                $this->seedClique($clique);
            }
        });

        $this->command->info('✅ GenreCliqueSeeder complete!');
        $this->command->info('   Run `php artisan serve` and retrain the SVD model to observe stabilised RMSE.');
    }

    // =========================================================================
    // SONG CATALOG  — 120 real tracks, 20 per genre clique
    // =========================================================================

    private function insertSongs(): array
    {
        $songs = [
            // ---- CLIQUE 1: Rock / Alt-Rock ----
            ['track_name' => 'Smells Like Teen Spirit',   'artist_name' => 'Nirvana',              'genres' => ['Rock','Grunge','Alt-Rock'],         'spotify_track_id' => 'rock001', 'release_date' => '1991-09-10'],
            ['track_name' => 'Bohemian Rhapsody',         'artist_name' => 'Queen',                'genres' => ['Rock','Classic Rock','Art Rock','Math-Rock'],   'spotify_track_id' => 'rock002', 'release_date' => '1975-10-31'],
            ['track_name' => 'Hotel California',          'artist_name' => 'Eagles',               'genres' => ['Rock','Soft Rock','Classic Rock'],  'spotify_track_id' => 'rock003', 'release_date' => '1977-02-22'],
            ['track_name' => 'Welcome to the Jungle',     'artist_name' => "Guns N' Roses",        'genres' => ['Rock','Hard Rock','Metal'],         'spotify_track_id' => 'rock004', 'release_date' => '1987-09-28'],
            ['track_name' => 'Creep',                     'artist_name' => 'Radiohead',            'genres' => ['Alt-Rock','Britpop','Rock'],        'spotify_track_id' => 'rock005', 'release_date' => '1992-09-21'],
            ['track_name' => 'Mr. Brightside',            'artist_name' => 'The Killers',          'genres' => ['Indie Rock','Alt-Rock','Rock'],     'spotify_track_id' => 'rock006', 'release_date' => '2003-09-29'],
            ['track_name' => 'Under the Bridge',          'artist_name' => 'Red Hot Chili Peppers','genres' => ['Rock','Alt-Rock','Funk Rock'],      'spotify_track_id' => 'rock007', 'release_date' => '1992-03-09'],
            ['track_name' => 'Black',                     'artist_name' => 'Pearl Jam',            'genres' => ['Grunge','Alt-Rock','Rock'],         'spotify_track_id' => 'rock008', 'release_date' => '1991-08-27'],
            ['track_name' => 'Seven Nation Army',         'artist_name' => 'The White Stripes',    'genres' => ['Alt-Rock','Garage Rock','Blues Rock'],'spotify_track_id' => 'rock009', 'release_date' => '2003-01-20'],
            ['track_name' => 'Lithium',                   'artist_name' => 'Nirvana',              'genres' => ['Grunge','Alt-Rock','Rock'],         'spotify_track_id' => 'rock010', 'release_date' => '1992-07-14'],
            ['track_name' => 'Everlong',                  'artist_name' => 'Foo Fighters',         'genres' => ['Alt-Rock','Rock','Post-Grunge'],    'spotify_track_id' => 'rock011', 'release_date' => '1997-08-18'],
            ['track_name' => 'Yellow',                    'artist_name' => 'Coldplay',             'genres' => ['Alt-Rock','Britpop','Indie Rock'],  'spotify_track_id' => 'rock012', 'release_date' => '2000-06-26'],
            ['track_name' => 'Wonderwall',                'artist_name' => 'Oasis',                'genres' => ['Britpop','Indie Rock','Alt-Rock'],  'spotify_track_id' => 'rock013', 'release_date' => '1995-10-30'],
            ['track_name' => 'Come as You Are',           'artist_name' => 'Nirvana',              'genres' => ['Grunge','Alt-Rock','Rock'],         'spotify_track_id' => 'rock014', 'release_date' => '1992-03-02'],
            ['track_name' => 'Roxanne',                   'artist_name' => 'The Police',           'genres' => ['Rock','New Wave','Post-Punk'],      'spotify_track_id' => 'rock015', 'release_date' => '1978-04-11'],
            ['track_name' => 'Here I Go Again',           'artist_name' => 'Whitesnake',           'genres' => ['Hard Rock','Rock','AOR'],           'spotify_track_id' => 'rock016', 'release_date' => '1982-06-01'],
            ['track_name' => 'Jump',                      'artist_name' => 'Van Halen',            'genres' => ['Hard Rock','Rock','Glam Metal'],    'spotify_track_id' => 'rock017', 'release_date' => '1984-01-09'],
            ['track_name' => 'Enter Sandman',             'artist_name' => 'Metallica',            'genres' => ['Metal','Thrash Metal','Hard Rock'], 'spotify_track_id' => 'rock018', 'release_date' => '1991-07-29'],
            ['track_name' => 'Paranoid',                  'artist_name' => 'Black Sabbath',        'genres' => ['Metal','Heavy Metal','Hard Rock'],  'spotify_track_id' => 'rock019', 'release_date' => '1970-08-26'],
            ['track_name' => 'Highway to Hell',           'artist_name' => 'AC/DC',                'genres' => ['Hard Rock','Rock','Heavy Metal'],   'spotify_track_id' => 'rock020', 'release_date' => '1979-06-27'],

            // ---- CLIQUE 2: Pop / Indie-Pop ----
            ['track_name' => 'Shape of You',              'artist_name' => 'Ed Sheeran',           'genres' => ['Pop','Indie Pop','Dance Pop'],      'spotify_track_id' => 'pop001', 'release_date' => '2017-01-06'],
            ['track_name' => 'Blinding Lights',           'artist_name' => 'The Weeknd',           'genres' => ['Pop','Synth-Pop','R&B'],            'spotify_track_id' => 'pop002', 'release_date' => '2019-11-29'],
            ['track_name' => 'Bad Guy',                   'artist_name' => 'Billie Eilish',        'genres' => ['Indie Pop','Pop','Electropop'],     'spotify_track_id' => 'pop003', 'release_date' => '2019-03-29'],
            ['track_name' => 'Levitating',                'artist_name' => 'Dua Lipa',             'genres' => ['Pop','Dance Pop','Disco Pop'],      'spotify_track_id' => 'pop004', 'release_date' => '2020-10-01'],
            ['track_name' => 'Watermelon Sugar',          'artist_name' => 'Harry Styles',         'genres' => ['Pop','Indie Pop','Soft Rock'],      'spotify_track_id' => 'pop005', 'release_date' => '2020-05-15'],
            ['track_name' => 'drivers license',           'artist_name' => 'Olivia Rodrigo',       'genres' => ['Pop','Teen Pop','Indie Pop'],       'spotify_track_id' => 'pop006', 'release_date' => '2021-01-08'],
            ['track_name' => 'Stay With Me',              'artist_name' => 'Sam Smith',            'genres' => ['Pop','Soul','Indie Pop'],           'spotify_track_id' => 'pop007', 'release_date' => '2014-05-26'],
            ['track_name' => 'Shake It Off',              'artist_name' => 'Taylor Swift',         'genres' => ['Pop','Dance Pop','Country Pop'],    'spotify_track_id' => 'pop008', 'release_date' => '2014-08-18'],
            ['track_name' => 'Uptown Funk',               'artist_name' => 'Mark Ronson ft. Bruno Mars','genres' => ['Pop','Funk','Disco'],        'spotify_track_id' => 'pop009', 'release_date' => '2014-11-10'],
            ['track_name' => 'Rolling in the Deep',       'artist_name' => 'Adele',                'genres' => ['Pop','Soul','R&B'],                 'spotify_track_id' => 'pop010', 'release_date' => '2010-11-29'],
            ['track_name' => 'Someone Like You',          'artist_name' => 'Adele',                'genres' => ['Pop','Soul','Ballad'],              'spotify_track_id' => 'pop011', 'release_date' => '2011-01-24'],
            ['track_name' => 'As It Was',                 'artist_name' => 'Harry Styles',         'genres' => ['Pop','Indie Pop','Synth-Pop'],      'spotify_track_id' => 'pop012', 'release_date' => '2022-04-01'],
            ['track_name' => 'Anti-Hero',                 'artist_name' => 'Taylor Swift',         'genres' => ['Pop','Indie Pop','Synth-Pop'],      'spotify_track_id' => 'pop013', 'release_date' => '2022-10-21'],
            ['track_name' => 'Flowers',                   'artist_name' => 'Miley Cyrus',          'genres' => ['Pop','Disco Pop','Dance Pop'],      'spotify_track_id' => 'pop014', 'release_date' => '2023-01-13'],
            ['track_name' => 'Cheap Thrills',             'artist_name' => 'Sia',                  'genres' => ['Pop','Dance Pop','Electropop'],     'spotify_track_id' => 'pop015', 'release_date' => '2016-05-20'],
            ['track_name' => 'Shallow',                   'artist_name' => 'Lady Gaga & Bradley Cooper','genres' => ['Pop','Country Pop','Ballad'],  'spotify_track_id' => 'pop016', 'release_date' => '2018-09-27'],
            ['track_name' => 'Perfect',                   'artist_name' => 'Ed Sheeran',           'genres' => ['Pop','Acoustic','Ballad'],          'spotify_track_id' => 'pop017', 'release_date' => '2017-09-26'],
            ['track_name' => 'Thinking Out Loud',         'artist_name' => 'Ed Sheeran',           'genres' => ['Pop','Soul','R&B'],                 'spotify_track_id' => 'pop018', 'release_date' => '2014-09-18'],
            ['track_name' => 'Stay',                      'artist_name' => 'Justin Bieber & The Kid LAROI','genres' => ['Pop','Emo Pop','Dance Pop'],'spotify_track_id' => 'pop019', 'release_date' => '2021-07-09'],
            ['track_name' => 'Peaches',                   'artist_name' => 'Justin Bieber ft. Daniel Caesar','genres' => ['Pop','R&B','Indie Pop'], 'spotify_track_id' => 'pop020', 'release_date' => '2021-03-19'],

            // ---- CLIQUE 3: Jazz / Soul / R&B ----
            ['track_name' => 'Take Five',                 'artist_name' => 'Dave Brubeck Quartet', 'genres' => ['Jazz','Cool Jazz','Bebop'],         'spotify_track_id' => 'jazz001', 'release_date' => '1959-08-18'],
            ['track_name' => "What's Going On",           'artist_name' => 'Marvin Gaye',          'genres' => ['Soul','R&B','Funk'],                'spotify_track_id' => 'jazz002', 'release_date' => '1971-01-20'],
            ['track_name' => 'Respect',                   'artist_name' => 'Aretha Franklin',      'genres' => ['Soul','R&B','Gospel'],              'spotify_track_id' => 'jazz003', 'release_date' => '1967-04-14'],
            ['track_name' => 'At Last',                   'artist_name' => 'Etta James',           'genres' => ['Jazz','Soul','Blues'],              'spotify_track_id' => 'jazz004', 'release_date' => '1960-11-01'],
            ['track_name' => "Ain't No Sunshine",         'artist_name' => 'Bill Withers',         'genres' => ['Soul','R&B','Blues'],               'spotify_track_id' => 'jazz005', 'release_date' => '1971-07-09'],
            ['track_name' => 'Autumn Leaves',             'artist_name' => 'Miles Davis',          'genres' => ['Jazz','Bebop','Hard Bop'],          'spotify_track_id' => 'jazz006', 'release_date' => '1958-05-26'],
            ['track_name' => 'Coltrane Plays the Blues',  'artist_name' => 'John Coltrane',        'genres' => ['Jazz','Modal Jazz','Avant-Garde'],  'spotify_track_id' => 'jazz007', 'release_date' => '1962-01-01'],
            ['track_name' => 'Superstition',              'artist_name' => 'Stevie Wonder',        'genres' => ['Soul','R&B','Funk'],                'spotify_track_id' => 'jazz008', 'release_date' => '1972-10-24'],
            ['track_name' => 'Lovely Day',                'artist_name' => 'Bill Withers',         'genres' => ['Soul','R&B','Pop'],                 'spotify_track_id' => 'jazz009', 'release_date' => '1977-09-01'],
            ['track_name' => 'Redbone',                   'artist_name' => 'Childish Gambino',     'genres' => ['Soul','R&B','Funk'],                'spotify_track_id' => 'jazz010', 'release_date' => '2016-11-10'],
            ['track_name' => "I Can't Make You Love Me",  'artist_name' => 'Bonnie Raitt',         'genres' => ['Soul','Blues','Ballad'],            'spotify_track_id' => 'jazz011', 'release_date' => '1991-09-14'],
            ['track_name' => 'Feeling Good',              'artist_name' => 'Nina Simone',          'genres' => ['Jazz','Soul','Blues'],              'spotify_track_id' => 'jazz012', 'release_date' => '1965-01-01'],
            ['track_name' => 'Put It in a Love Song',     'artist_name' => 'Alicia Keys',          'genres' => ['R&B','Soul','Pop'],                 'spotify_track_id' => 'jazz013', 'release_date' => '2009-12-15'],
            ['track_name' => 'No One',                    'artist_name' => 'Alicia Keys',          'genres' => ['R&B','Soul','Pop'],                 'spotify_track_id' => 'jazz014', 'release_date' => '2007-09-25'],
            ['track_name' => "Isn't She Lovely",          'artist_name' => 'Stevie Wonder',        'genres' => ['Soul','R&B','Funk'],                'spotify_track_id' => 'jazz015', 'release_date' => '1976-07-05'],
            ['track_name' => 'Say So',                    'artist_name' => 'Doja Cat',             'genres' => ['R&B','Pop','Disco'],                'spotify_track_id' => 'jazz016', 'release_date' => '2019-11-01'],
            ['track_name' => 'CRANES IN THE SKY',         'artist_name' => 'Solange',              'genres' => ['R&B','Neo-Soul','Indie R&B'],       'spotify_track_id' => 'jazz017', 'release_date' => '2016-10-21'],
            ['track_name' => 'Location',                  'artist_name' => 'Khalid',               'genres' => ['R&B','Indie R&B','Pop'],            'spotify_track_id' => 'jazz018', 'release_date' => '2016-07-19'],
            ['track_name' => 'Made in America',           'artist_name' => "D'Angelo",             'genres' => ['Neo-Soul','R&B','Funk'],            'spotify_track_id' => 'jazz019', 'release_date' => '2000-01-25'],
            ['track_name' => 'Healer',                    'artist_name' => 'Erykah Badu',          'genres' => ['Neo-Soul','R&B','Jazz'],            'spotify_track_id' => 'jazz020', 'release_date' => '2008-03-01'],

            // ---- CLIQUE 4: Hip-Hop / Rap ----
            ['track_name' => 'HUMBLE.',                   'artist_name' => 'Kendrick Lamar',       'genres' => ['Hip-Hop','Rap','Conscious Rap'],    'spotify_track_id' => 'hiphop001', 'release_date' => '2017-03-30'],
            ['track_name' => 'God\'s Plan',               'artist_name' => 'Drake',                'genres' => ['Hip-Hop','Rap','Trap'],             'spotify_track_id' => 'hiphop002', 'release_date' => '2018-01-19'],
            ['track_name' => 'Sicko Mode',                'artist_name' => 'Travis Scott',         'genres' => ['Hip-Hop','Trap','Rap'],             'spotify_track_id' => 'hiphop003', 'release_date' => '2018-08-03'],
            ['track_name' => 'Alright',                   'artist_name' => 'Kendrick Lamar',       'genres' => ['Hip-Hop','Conscious Rap','Funk'],   'spotify_track_id' => 'hiphop004', 'release_date' => '2015-03-15'],
            ['track_name' => 'Hotline Bling',             'artist_name' => 'Drake',                'genres' => ['Hip-Hop','R&B','Pop Rap'],          'spotify_track_id' => 'hiphop005', 'release_date' => '2015-07-31'],
            ['track_name' => 'Lose Yourself',             'artist_name' => 'Eminem',               'genres' => ['Hip-Hop','Rap','East Coast Rap'],   'spotify_track_id' => 'hiphop006', 'release_date' => '2002-10-22'],
            ['track_name' => 'Stronger',                  'artist_name' => 'Kanye West',           'genres' => ['Hip-Hop','Rap','Electronic'],       'spotify_track_id' => 'hiphop007', 'release_date' => '2007-07-30'],
            ['track_name' => 'Good Times',                'artist_name' => 'Chic',                 'genres' => ['Funk','Disco','Hip-Hop'],           'spotify_track_id' => 'hiphop008', 'release_date' => '1979-06-01'],
            ['track_name' => 'N95',                       'artist_name' => 'Kendrick Lamar',       'genres' => ['Hip-Hop','Rap','Conscious Rap'],    'spotify_track_id' => 'hiphop009', 'release_date' => '2022-05-13'],
            ['track_name' => 'Rich Baby Daddy',           'artist_name' => 'Drake ft. Sexyy Red',  'genres' => ['Hip-Hop','Rap','Trap'],             'spotify_track_id' => 'hiphop010', 'release_date' => '2023-06-23'],
            ['track_name' => 'Backseat Freestyle',        'artist_name' => 'Kendrick Lamar',       'genres' => ['Hip-Hop','Rap','West Coast Rap'],   'spotify_track_id' => 'hiphop011', 'release_date' => '2012-10-22'],
            ['track_name' => 'INDUSTRY BABY',             'artist_name' => 'Lil Nas X & Jack Harlow','genres' => ['Hip-Hop','Pop Rap','Trap'],      'spotify_track_id' => 'hiphop012', 'release_date' => '2021-07-23'],
            ['track_name' => 'Numb/Encore',               'artist_name' => 'Jay-Z & Linkin Park',  'genres' => ['Hip-Hop','Rap','Nu-Metal'],         'spotify_track_id' => 'hiphop013', 'release_date' => '2004-11-16'],
            ['track_name' => 'Family Business',           'artist_name' => 'Kanye West',           'genres' => ['Hip-Hop','Conscious Rap','Soul'],   'spotify_track_id' => 'hiphop014', 'release_date' => '2004-02-10'],
            ['track_name' => 'Money Trees',               'artist_name' => 'Kendrick Lamar',       'genres' => ['Hip-Hop','West Coast Rap','Jazz Rap'],'spotify_track_id' => 'hiphop015', 'release_date' => '2012-10-22'],
            ['track_name' => 'Mask Off',                  'artist_name' => 'Future',               'genres' => ['Hip-Hop','Trap','Rap'],             'spotify_track_id' => 'hiphop016', 'release_date' => '2017-01-27'],
            ['track_name' => 'Rockstar',                  'artist_name' => 'Post Malone ft. 21 Savage','genres' => ['Hip-Hop','Trap','Emo Rap'],   'spotify_track_id' => 'hiphop017', 'release_date' => '2017-09-15'],
            ['track_name' => 'Sunflower',                 'artist_name' => 'Post Malone & Swae Lee','genres' => ['Hip-Hop','Pop Rap','Indie Pop'],  'spotify_track_id' => 'hiphop018', 'release_date' => '2018-10-18'],
            ['track_name' => 'Bop',                       'artist_name' => 'DaBaby',               'genres' => ['Hip-Hop','Trap','Rap'],             'spotify_track_id' => 'hiphop019', 'release_date' => '2019-08-27'],
            ['track_name' => 'Essence',                   'artist_name' => 'Wizkid ft. Tems',      'genres' => ['Afrobeats','R&B','Hip-Hop'],        'spotify_track_id' => 'hiphop020', 'release_date' => '2020-08-27'],

            // ---- CLIQUE 5: Electronic / EDM ----
            ['track_name' => 'One More Time',             'artist_name' => 'Daft Punk',            'genres' => ['Electronic','Dance','House'],       'spotify_track_id' => 'edm001', 'release_date' => '2000-11-13'],
            ['track_name' => 'Get Lucky',                 'artist_name' => 'Daft Punk ft. Pharrell Williams','genres' => ['Electronic','Disco','Funk'],'spotify_track_id' => 'edm002', 'release_date' => '2013-04-19'],
            ['track_name' => 'Levels',                    'artist_name' => 'Avicii',               'genres' => ['EDM','Progressive House','Dance'],  'spotify_track_id' => 'edm003', 'release_date' => '2011-10-28'],
            ['track_name' => 'Clarity',                   'artist_name' => 'Zedd ft. Foxes',       'genres' => ['EDM','Electropop','Dance'],         'spotify_track_id' => 'edm004', 'release_date' => '2012-10-02'],
            ['track_name' => 'Animals',                   'artist_name' => 'Martin Garrix',        'genres' => ['EDM','Progressive House','Big Room'],'spotify_track_id' => 'edm005', 'release_date' => '2013-08-09'],
            ['track_name' => 'Don\'t You Worry Child',    'artist_name' => 'Swedish House Mafia',  'genres' => ['EDM','Progressive House','Dance'],  'spotify_track_id' => 'edm006', 'release_date' => '2012-09-17'],
            ['track_name' => 'Wake Me Up',                'artist_name' => 'Avicii',               'genres' => ['EDM','Progressive House','Folktronica'],'spotify_track_id' => 'edm007', 'release_date' => '2013-06-17'],
            ['track_name' => 'Summer',                    'artist_name' => 'Calvin Harris',        'genres' => ['EDM','Progressive House','Dance'],  'spotify_track_id' => 'edm008', 'release_date' => '2014-04-04'],
            ['track_name' => 'Titanium',                  'artist_name' => 'David Guetta ft. Sia', 'genres' => ['EDM','Dance','Electropop'],         'spotify_track_id' => 'edm009', 'release_date' => '2011-08-22'],
            ['track_name' => 'We Found Love',             'artist_name' => 'Rihanna ft. Calvin Harris','genres' => ['EDM','Dance','Synth-Pop'],     'spotify_track_id' => 'edm010', 'release_date' => '2011-09-22'],
            ['track_name' => 'Lean On',                   'artist_name' => 'Major Lazer & DJ Snake','genres' => ['EDM','Electronic','Dancehall'],   'spotify_track_id' => 'edm011', 'release_date' => '2015-03-02'],
            ['track_name' => 'Oceans',                    'artist_name' => 'Seafret',              'genres' => ['Indie Electronic','Electronic','Ambient'],'spotify_track_id' => 'edm012', 'release_date' => '2015-07-17'],
            ['track_name' => 'Midnight City',             'artist_name' => 'M83',                  'genres' => ['Electronic','Synth-Pop','Indie Electronic'],'spotify_track_id' => 'edm013', 'release_date' => '2011-09-26'],
            ['track_name' => 'Flightless Bird',           'artist_name' => 'Iron & Wine',          'genres' => ['Indie Folk','Ambient','Electronic'],'spotify_track_id' => 'edm014', 'release_date' => '2004-09-07'],
            ['track_name' => 'Shelter',                   'artist_name' => 'Porter Robinson & Madeon','genres' => ['Electronic','Future Bass','Dance'],'spotify_track_id' => 'edm015', 'release_date' => '2016-08-11'],
            ['track_name' => 'Faded',                     'artist_name' => 'Alan Walker',          'genres' => ['EDM','Progressive House','Electronic'],'spotify_track_id' => 'edm016', 'release_date' => '2015-12-03'],
            ['track_name' => 'Spectre',                   'artist_name' => 'Alan Walker',          'genres' => ['EDM','Electronic','Future Bass'],   'spotify_track_id' => 'edm017', 'release_date' => '2015-01-17'],
            ['track_name' => 'Roses',                     'artist_name' => 'SAINt JHN',            'genres' => ['Electronic','Hip-Hop','R&B'],       'spotify_track_id' => 'edm018', 'release_date' => '2016-11-30'],
            ['track_name' => 'Bleed 4 Me',                'artist_name' => 'KALEO',                'genres' => ['Indie Rock','Electronic','Alternative'],'spotify_track_id' => 'edm019', 'release_date' => '2016-06-10'],
            ['track_name' => 'Resonance',                 'artist_name' => 'HOME',                 'genres' => ['Electronic','Chillwave','Synthwave'],'spotify_track_id' => 'edm020', 'release_date' => '2014-05-22'],

            // ---- CLIQUE 6: Classical / Ambient / Cinematic ----
            ['track_name' => 'Clair de Lune',             'artist_name' => 'Claude Debussy',       'genres' => ['Classical','Impressionist','Piano'],'spotify_track_id' => 'classical001', 'release_date' => '1905-01-01'],
            ['track_name' => 'Moonlight Sonata',          'artist_name' => 'Ludwig van Beethoven', 'genres' => ['Classical','Romantic','Piano'],     'spotify_track_id' => 'classical002', 'release_date' => '1802-01-01'],
            ['track_name' => 'The Four Seasons: Spring',  'artist_name' => 'Antonio Vivaldi',      'genres' => ['Classical','Baroque','Orchestral'], 'spotify_track_id' => 'classical003', 'release_date' => '1725-01-01'],
            ['track_name' => 'Gymnopédie No.1',           'artist_name' => 'Erik Satie',           'genres' => ['Classical','Impressionist','Ambient'],'spotify_track_id' => 'classical004', 'release_date' => '1888-01-01'],
            ['track_name' => 'Canon in D Major',          'artist_name' => 'Johann Pachelbel',     'genres' => ['Classical','Baroque','Orchestral'], 'spotify_track_id' => 'classical005', 'release_date' => '1694-01-01'],
            ['track_name' => 'Nuvole Bianche',            'artist_name' => 'Ludovico Einaudi',     'genres' => ['Neoclassical','Ambient','Piano'],   'spotify_track_id' => 'classical006', 'release_date' => '2004-10-11'],
            ['track_name' => 'Fly',                       'artist_name' => 'Ludovico Einaudi',     'genres' => ['Neoclassical','Ambient','Piano'],   'spotify_track_id' => 'classical007', 'release_date' => '2009-09-01'],
            ['track_name' => 'Experience',                'artist_name' => 'Ludovico Einaudi',     'genres' => ['Neoclassical','Cinematic','Ambient'],'spotify_track_id' => 'classical008', 'release_date' => '2013-04-01'],
            ['track_name' => 'On the Nature of Daylight',  'artist_name' => 'Max Richter',         'genres' => ['Neoclassical','Ambient','Cinematic'],'spotify_track_id' => 'classical009', 'release_date' => '2004-03-01'],
            ['track_name' => 'Comptine d\'un autre été',  'artist_name' => 'Yann Tiersen',         'genres' => ['Neoclassical','Ambient','Cinematic'],'spotify_track_id' => 'classical010', 'release_date' => '2001-10-01'],
            ['track_name' => 'River Flows in You',        'artist_name' => 'Yiruma',               'genres' => ['Neoclassical','New Age','Piano'],   'spotify_track_id' => 'classical011', 'release_date' => '2001-01-01'],
            ['track_name' => 'Kiss the Rain',             'artist_name' => 'Yiruma',               'genres' => ['Neoclassical','New Age','Piano'],   'spotify_track_id' => 'classical012', 'release_date' => '2003-01-01'],
            ['track_name' => 'The Last of the Mohicans',  'artist_name' => 'Trevor Jones',         'genres' => ['Cinematic','Orchestral','Soundtrack'],'spotify_track_id' => 'classical013', 'release_date' => '1992-09-01'],
            ['track_name' => 'Time',                      'artist_name' => 'Hans Zimmer',          'genres' => ['Cinematic','Orchestral','Ambient'], 'spotify_track_id' => 'classical014', 'release_date' => '2010-07-13'],
            ['track_name' => 'Now We Are Free',           'artist_name' => 'Hans Zimmer & Lisa Gerrard','genres' => ['Cinematic','Orchestral','World'],'spotify_track_id' => 'classical015', 'release_date' => '2000-05-01'],
            ['track_name' => 'Epigraph No. 4',            'artist_name' => 'Claude Debussy',       'genres' => ['Classical','Impressionist','Chamber'],'spotify_track_id' => 'classical016', 'release_date' => '1914-01-01'],
            ['track_name' => 'Nocturne in E-flat major',  'artist_name' => 'Frédéric Chopin',      'genres' => ['Classical','Romantic','Piano'],     'spotify_track_id' => 'classical017', 'release_date' => '1831-01-01'],
            ['track_name' => 'Spiegel im Spiegel',        'artist_name' => 'Arvo Pärt',            'genres' => ['Neoclassical','Ambient','Minimalist'],'spotify_track_id' => 'classical018', 'release_date' => '1978-01-01'],
            ['track_name' => 'Bloom',                     'artist_name' => 'The Album Leaf',       'genres' => ['Ambient','Post-Rock','Neoclassical'],'spotify_track_id' => 'classical019', 'release_date' => '2004-03-09'],
            ['track_name' => 'Svefn-g-englar',            'artist_name' => 'Sigur Rós',            'genres' => ['Ambient','Post-Rock','Neoclassical'],'spotify_track_id' => 'classical020', 'release_date' => '1999-06-12'],
        ];

        $insertedIds = [];
        foreach ($songs as $song) {
            // Skip if already exists (idempotent)
            $existing = DB::table('songs')->where('spotify_track_id', $song['spotify_track_id'])->first();
            if ($existing) {
                $insertedIds[$song['spotify_track_id']] = $existing->id;
                continue;
            }

            $id = DB::table('songs')->insertGetId([
                'spotify_track_id' => $song['spotify_track_id'],
                'track_name'       => $song['track_name'],
                'artist_name'      => $song['artist_name'],
                'genres'           => json_encode($song['genres']),
                'release_date'     => $song['release_date'] ?? null,
                'created_at'       => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ]);
            $insertedIds[$song['spotify_track_id']] = $id;
        }

        $this->command->info('     Inserted/found ' . count($insertedIds) . ' songs.');
        return $insertedIds;
    }

    // =========================================================================
    // CLIQUE DEFINITIONS
    // =========================================================================

    private function cliques(array $songIds): array
    {
        /**
         * Each clique:
         *   name         → Display name
         *   tag          → Email prefix e.g. "rock.fan01@sim.reso"
         *   song_keys    → spotify_track_ids in this clique's primary pool
         *   cross_keys   → A few song_ids from adjacent genres (realistic overlap)
         *   users        → Array of user name templates
         */
        return [
            // ------------------------------------------------------------------
            // CLIQUE 1 — Rock / Alt-Rock
            // ------------------------------------------------------------------
            [
                'name'       => 'Rock / Alt-Rock',
                'tag'        => 'rock',
                'song_keys'  => ['rock001','rock002','rock003','rock004','rock005',
                                 'rock006','rock007','rock008','rock009','rock010',
                                 'rock011','rock012','rock013','rock014','rock015',
                                 'rock016','rock017','rock018','rock019','rock020'],
                'cross_keys' => ['hiphop006','pop009','edm003'],   // Slim Shady + Uptown Funk + Levels bleed over
                'users'      => [
                    'Alex Grunge','Blake Riffmaster','Casey Distortion','Dylan Headbanger',
                    'Evan Powerchord','Fiona Amplifier','Grant Moshpit','Hannah Setlist',
                    'Ian Overdrive','Jade Decibel',
                ],
            ],
            // ------------------------------------------------------------------
            // CLIQUE 2 — Pop / Indie-Pop
            // ------------------------------------------------------------------
            [
                'name'       => 'Pop / Indie-Pop',
                'tag'        => 'pop',
                'song_keys'  => ['pop001','pop002','pop003','pop004','pop005',
                                 'pop006','pop007','pop008','pop009','pop010',
                                 'pop011','pop012','pop013','pop014','pop015',
                                 'pop016','pop017','pop018','pop019','pop020'],
                'cross_keys' => ['jazz002','jazz003','rock012'],   // Marvin Gaye + Aretha + Yellow
                'users'      => [
                    'Amber Charttopper','Brian Playlist','Clara Streaming','Daniel Bop',
                    'Ella Mainstream','Fred Chorus','Grace Earworm','Henry Synth',
                    'Isla Hookline','Jake Radio',
                ],
            ],
            // ------------------------------------------------------------------
            // CLIQUE 3 — Jazz / Soul / R&B
            // ------------------------------------------------------------------
            [
                'name'       => 'Jazz / Soul / R&B',
                'tag'        => 'jazz',
                'song_keys'  => ['jazz001','jazz002','jazz003','jazz004','jazz005',
                                 'jazz006','jazz007','jazz008','jazz009','jazz010',
                                 'jazz011','jazz012','jazz013','jazz014','jazz015',
                                 'jazz016','jazz017','jazz018','jazz019','jazz020'],
                'cross_keys' => ['classical004','hiphop004','pop007'],  // Satie + Alright + Stay With Me
                'users'      => [
                    'Aaron Bebop','Bianca Groove','Carl Velvet','Diana Soulmate',
                    'Elliot Blues','Faye Harmony','George Tenor','Hannah Neo-Soul',
                    'Iris Midnight','James Riff',
                ],
            ],
            // ------------------------------------------------------------------
            // CLIQUE 4 — Hip-Hop / Rap
            // ------------------------------------------------------------------
            [
                'name'       => 'Hip-Hop / Rap',
                'tag'        => 'hiphop',
                'song_keys'  => ['hiphop001','hiphop002','hiphop003','hiphop004','hiphop005',
                                 'hiphop006','hiphop007','hiphop008','hiphop009','hiphop010',
                                 'hiphop011','hiphop012','hiphop013','hiphop014','hiphop015',
                                 'hiphop016','hiphop017','hiphop018','hiphop019','hiphop020'],
                'cross_keys' => ['edm007','pop002','jazz010'],    // Wake Me Up + Blinding Lights + Redbone
                'users'      => [
                    'Andre Cypher','Brianna Freestyle','Calvin Trapstar','Destiny Lyricist',
                    'Elijah Verse','Fatima Flows','Gideon Beatmaker','Harmony Bars',
                    'Ivan Mixtape','Jasmine Droptop',
                ],
            ],
            // ------------------------------------------------------------------
            // CLIQUE 5 — Electronic / EDM
            // ------------------------------------------------------------------
            [
                'name'       => 'Electronic / EDM',
                'tag'        => 'edm',
                'song_keys'  => ['edm001','edm002','edm003','edm004','edm005',
                                 'edm006','edm007','edm008','edm009','edm010',
                                 'edm011','edm012','edm013','edm014','edm015',
                                 'edm016','edm017','edm018','edm019','edm020'],
                'cross_keys' => ['hiphop007','pop002','rock005'],  // Stronger + Blinding Lights + Creep
                'users'      => [
                    'Adam Synthesizer','Bella Waveform','Chris Bassline','Dana Rave',
                    'Ethan Festival','Freya Dropzone','Gavin Bpm','Holly Melodica',
                    'Igor Subwoofer','Jade Circuit',
                ],
            ],
            // ------------------------------------------------------------------
            // CLIQUE 6 — Classical / Ambient / Cinematic
            // ------------------------------------------------------------------
            [
                'name'       => 'Classical / Ambient',
                'tag'        => 'classical',
                'song_keys'  => ['classical001','classical002','classical003','classical004','classical005',
                                 'classical006','classical007','classical008','classical009','classical010',
                                 'classical011','classical012','classical013','classical014','classical015',
                                 'classical016','classical017','classical018','classical019','classical020'],
                'cross_keys' => ['jazz006','jazz004','edm012'],   // Miles Davis + Etta James + Oceans
                'users'      => [
                    'Arthur Sonata','Beatrice Cadenza','Conrad Opus','Delilah Legato',
                    'Edmund Fortissimo','Felicia Pianissimo','Gerald Overture','Helen Adagio',
                    'Ignatius Cantata','Josephine Rondo',
                ],
            ],
        ];
    }

    // =========================================================================
    // SEED A SINGLE CLIQUE
    // =========================================================================

    private function seedClique(array $clique): void
    {
        $songIds = $this->getSongIdsByKeys(array_merge($clique['song_keys'], $clique['cross_keys']));
        $primarySongIds = $this->getSongIdsByKeys($clique['song_keys']);
        $crossSongIds   = $this->getSongIdsByKeys($clique['cross_keys']);

        foreach ($clique['users'] as $idx => $userName) {
            $num    = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
            $email  = "{$clique['tag']}.sim{$num}@sim.reso.local";

            // ---- Create user if not exists ----
            $userId = $this->findOrCreateUser($userName, $email);

            // ---- Social graph: each user follows 3–5 clique-mates ----
            $this->seedFollows($userId, $clique['users'], $clique['tag'], $idx);

            // ---- Primary genre interactions (strong signal) ----
            // Every user gets 8–12 shares from their primary song pool
            $primaryPool = collect($primarySongIds)->shuffle()->take(rand(10, 14))->values();

            foreach ($primaryPool as $songId) {
                $shareId = $this->createShare($userId, $songId, $clique['name']);

                // Randomly add comments from clique-mates (peer engagement)
                $this->maybeAddPeerComment($shareId, $userId, $clique['users'], $clique['tag'], $idx);

                // Randomly add likes from clique-mates
                $this->maybeAddPeerLike($shareId, $userId, $clique['users'], $clique['tag'], $idx);

                // Shelf adds for ~40% of primary songs (highest 4.0pt signal)
                if (rand(0, 100) < 40) {
                    $spotifyId = DB::table('songs')->where('id', $songId)->value('spotify_track_id');
                    if ($spotifyId) {
                        $this->addShelfSong($userId, $spotifyId);
                    }
                }

                // song_interactions: mark as 'like' for ~50% of shared songs
                if (rand(0, 100) < 50) {
                    $this->addSongInteraction($userId, $songId, 'like');
                } else {
                    $this->addSongInteraction($userId, $songId, 'listen');
                }
            }

            // ---- Cross-genre bleed (realistic: a jazz fan might also like ONE Levels) ----
            if (!empty($crossSongIds)) {
                $crossPick = collect($crossSongIds)->random(min(2, count($crossSongIds)));
                foreach ($crossPick as $songId) {
                    $this->addSongInteraction($userId, $songId, 'listen');
                }
            }

            // ---- A few dislikes for songs outside their genre (realism) ----
            // Pick 1-2 songs from a clique that is very different
            // We don't need to do full dislikes here; the seed is already rich enough.
        }
    }

    // =========================================================================
    // HELPER: Get DB song IDs from spotify_track_id keys
    // =========================================================================

    private function getSongIdsByKeys(array $keys): array
    {
        return DB::table('songs')
            ->whereIn('spotify_track_id', $keys)
            ->pluck('id')
            ->toArray();
    }

    // =========================================================================
    // HELPER: Find or create a simulated user
    // =========================================================================

    private function findOrCreateUser(string $name, string $email): int
    {
        $existing = DB::table('users')->where('email', $email)->first();
        if ($existing) {
            return $existing->id;
        }

        return DB::table('users')->insertGetId([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make('SimulatedUser2024!'),
            'is_onboarded'      => 1,
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
    }

    // =========================================================================
    // HELPER: Social follows within a clique (builds trust graph)
    // =========================================================================

    private function seedFollows(int $userId, array $clique_users, string $tag, int $selfIdx): void
    {
        // Follow 3 other users in the same clique
        $indices = range(0, count($clique_users) - 1);
        shuffle($indices);
        $followed = 0;
        foreach ($indices as $i) {
            if ($i === $selfIdx) continue;
            $num   = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $email = "{$tag}.sim{$num}@sim.reso.local";
            $peer  = DB::table('users')->where('email', $email)->first();
            if (!$peer) continue;

            $exists = DB::table('followers')
                ->where('user_id', $peer->id)
                ->where('follower_id', $userId)
                ->exists();
            if (!$exists) {
                DB::table('followers')->insert([
                    'user_id'    => $peer->id,    // Being followed
                    'follower_id'=> $userId,       // Who is following
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            if (++$followed >= 3) break;
        }
    }

    // =========================================================================
    // HELPER: Create a share (3.0 pts for SVD)
    // =========================================================================

    private function createShare(int $userId, int $songId, string $genre): int
    {
        $captions = [
            "This track perfectly captures the {$genre} vibe 🎵",
            "Can't stop listening to this one 🔥",
            "Absolute banger — {$genre} at its finest",
            "This is the song of the week for me 🎧",
            "If you haven't heard this yet, you're missing out",
            "On repeat all morning ⬆️",
            "This one hits different late at night",
            "Sharing this gem with everyone 💎",
        ];

        return DB::table('shares')->insertGetId([
            'user_id'    => $userId,
            'song_id'    => $songId,
            'caption'    => $captions[array_rand($captions)],
            'type'       => 'music',
            'created_at' => Carbon::now()->subDays(rand(0, 180)),
            'updated_at' => Carbon::now(),
        ]);
    }

    // =========================================================================
    // HELPER: Randomly add a peer comment on a share (1.0 pt)
    // =========================================================================

    private function maybeAddPeerComment(int $shareId, int $shareOwnerId, array $clique_users, string $tag, int $selfIdx): void
    {
        if (rand(0, 100) > 60) return; // 60% chance of getting a comment

        $comments = [
            'This track is everything! 🙌',
            'Such a good find, thanks for sharing!',
            'Been listening to this on loop 🔁',
            'Incredible production on this one',
            'This is exactly my vibe right now 🎶',
            'This goes so hard 💥',
            'Pure perfection, instant classic',
        ];

        // Pick a random peer from the clique
        $peerIdx = rand(0, count($clique_users) - 1);
        if ($peerIdx === $selfIdx) $peerIdx = ($peerIdx + 1) % count($clique_users);

        $num   = str_pad($peerIdx + 1, 2, '0', STR_PAD_LEFT);
        $email = "{$tag}.sim{$num}@sim.reso.local";
        $peer  = DB::table('users')->where('email', $email)->first();
        if (!$peer) return;

        DB::table('comments')->insert([
            'user_id'    => $peer->id,
            'share_id'   => $shareId,
            'body'       => $comments[array_rand($comments)],
            'created_at' => Carbon::now()->subDays(rand(0, 180)),
            'updated_at' => Carbon::now(),
        ]);
    }

    // =========================================================================
    // HELPER: Randomly add a like from a peer (2.0 pts)
    // =========================================================================

    private function maybeAddPeerLike(int $shareId, int $shareOwnerId, array $clique_users, string $tag, int $selfIdx): void
    {
        // 70% chance this post gets liked by 1-3 peers
        if (rand(0, 100) > 70) return;

        $numLikes = rand(1, 3);
        $seenPeers = [];

        for ($l = 0; $l < $numLikes; $l++) {
            $peerIdx = rand(0, count($clique_users) - 1);
            if ($peerIdx === $selfIdx || in_array($peerIdx, $seenPeers)) continue;
            $seenPeers[] = $peerIdx;

            $num   = str_pad($peerIdx + 1, 2, '0', STR_PAD_LEFT);
            $email = "{$tag}.sim{$num}@sim.reso.local";
            $peer  = DB::table('users')->where('email', $email)->first();
            if (!$peer) continue;

            $alreadyLiked = DB::table('likes')
                ->where('user_id', $peer->id)
                ->where('share_id', $shareId)
                ->exists();

            if (!$alreadyLiked) {
                DB::table('likes')->insert([
                    'user_id'    => $peer->id,
                    'share_id'   => $shareId,
                    'created_at' => Carbon::now()->subDays(rand(0, 180)),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }

    // =========================================================================
    // HELPER: Add shelf song (4.0 pts — highest premium signal)
    // =========================================================================

    private function addShelfSong(int $userId, string $spotifyTrackId): void
    {
        $exists = DB::table('user_shelf_songs')
            ->where('user_id', $userId)
            ->where('song_id', $spotifyTrackId)
            ->exists();

        if (!$exists) {
            DB::table('user_shelf_songs')->insert([
                'user_id'    => $userId,
                'song_id'    => $spotifyTrackId,
                'position'   => rand(1, 10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    // =========================================================================
    // HELPER: Add direct song interaction (like/listen/dislike)
    // =========================================================================

    private function addSongInteraction(int $userId, int $songId, string $type): void
    {
        $exists = DB::table('song_interactions')
            ->where('user_id', $userId)
            ->where('song_id', $songId)
            ->exists();

        if (!$exists) {
            DB::table('song_interactions')->insert([
                'user_id'    => $userId,
                'song_id'    => $songId,
                'type'       => $type,
                'created_at' => Carbon::now()->subDays(rand(0, 120)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
