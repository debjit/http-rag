<?php

namespace App\Console\Commands;

use App\Services\AiServices;
use App\Services\QdrantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class PopulateQdrantWithBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qdrant:populate-books
                            {collection? : The name of the Qdrant collection to use. Defaults to config value.}
                            {--chunk-size=10 : The number of books to process in each batch.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates the Qdrant database with a predefined list of books and their embeddings.';

    protected AiServices $aiService;

    protected QdrantService $qdrantService;

    private array $books = [
        // Harry Potter Series
        ['id' => 1, 'title' => 'Harry Potter and the Philosopher\'s Stone', 'author' => 'J.K. Rowling', 'language' => 'English', 'year' => 1997, 'copies_sold' => '120 million', 'genre' => 'Fantasy', 'gist' => 'A young orphan discovers he is a wizard and attends a magical school.'],
        ['id' => 2, 'title' => 'Harry Potter and the Chamber of Secrets', 'author' => 'J.K. Rowling', 'language' => 'English', 'year' => 1998, 'copies_sold' => '77 million', 'genre' => 'Fantasy', 'gist' => 'Harry returns to Hogwarts for his second year, where he uncovers a dark secret and faces a new threat.'],
        ['id' => 3, 'title' => 'Harry Potter and the Prisoner of Azkaban', 'author' => 'J.K. Rowling', 'language' => 'English', 'year' => 1999, 'copies_sold' => '65 million', 'genre' => 'Fantasy', 'gist' => 'Harry learns more about his past and confronts an escaped prisoner believed to be a dangerous supporter of Voldemort.'],
        ['id' => 4, 'title' => 'Harry Potter and the Goblet of Fire', 'author' => 'J.K. Rowling', 'language' => 'English', 'year' => 2000, 'copies_sold' => '65 million', 'genre' => 'Fantasy', 'gist' => 'Harry unexpectedly participates in the dangerous Triwizard Tournament, leading to a confrontation with Lord Voldemort.'],
        ['id' => 5, 'title' => 'Harry Potter and the Order of the Phoenix', 'author' => 'J.K. Rowling', 'language' => 'English', 'year' => 2003, 'copies_sold' => '65 million', 'genre' => 'Fantasy', 'gist' => 'Harry struggles with the Ministry of Magic\'s denial of Voldemort\'s return and forms a secret defense group.'],
        ['id' => 6, 'title' => 'Harry Potter and the Half-Blood Prince', 'author' => 'J.K. Rowling', 'language' => 'English', 'year' => 2005, 'copies_sold' => '65 million', 'genre' => 'Fantasy', 'gist' => 'Harry delves into Voldemort\'s past and prepares for the final battle, while suspecting Draco Malfoy of dark activities.'],
        ['id' => 7, 'title' => 'Harry Potter and the Deathly Hallows', 'author' => 'J.K. Rowling', 'language' => 'English', 'year' => 2007, 'copies_sold' => '65 million', 'genre' => 'Fantasy', 'gist' => 'Harry, Ron, and Hermione hunt for Horcruxes to destroy Voldemort, culminating in a final showdown at Hogwarts.'],

        // Other English Bestsellers
        ['id' => 8, 'title' => 'The Lord of the Rings', 'author' => 'J.R.R. Tolkien', 'language' => 'English', 'year' => 1954, 'copies_sold' => '150 million', 'genre' => 'Fantasy', 'gist' => 'A hobbit inherits a powerful, evil ring and embarks on a quest to destroy it.'],
        ['id' => 9, 'title' => 'The Da Vinci Code', 'author' => 'Dan Brown', 'language' => 'English', 'year' => 2003, 'copies_sold' => '80 million', 'genre' => 'Mystery Thriller', 'gist' => 'A symbologist uncovers a conspiracy related to the Holy Grail while solving a murder.'],
        ['id' => 10, 'title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'language' => 'English', 'year' => 1960, 'copies_sold' => '40 million', 'genre' => 'Southern Gothic, Bildungsroman', 'gist' => 'A young girl in the American South witnesses racial injustice as her lawyer father defends a black man.'],
        ['id' => 11, 'title' => '1984', 'author' => 'George Orwell', 'language' => 'English', 'year' => 1949, 'copies_sold' => '50 million', 'genre' => 'Dystopian, Science Fiction', 'gist' => 'A man living under a totalitarian regime struggles with oppression and surveillance.'],
        ['id' => 12, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'language' => 'English', 'year' => 1925, 'copies_sold' => '30 million', 'genre' => 'Tragedy, Modernist', 'gist' => 'A mysterious millionaire throws lavish parties in pursuit of his past love.'],
        ['id' => 13, 'title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'language' => 'English', 'year' => 1813, 'copies_sold' => '20 million', 'genre' => 'Romance, Satire', 'gist' => 'A witty young woman navigates societal expectations and romance in 19th-century England.'],
        ['id' => 14, 'title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'language' => 'English', 'year' => 1951, 'copies_sold' => '65 million', 'genre' => 'Coming-of-age, Realism', 'gist' => 'A cynical teenager recounts his experiences after being expelled from prep school.'],
        ['id' => 15, 'title' => 'The Hobbit', 'author' => 'J.R.R. Tolkien', 'language' => 'English', 'year' => 1937, 'copies_sold' => '100 million', 'genre' => 'Fantasy', 'gist' => 'A reluctant hobbit joins a group of dwarves on a quest to reclaim their treasure from a dragon.'],
        ['id' => 16, 'title' => 'The Chronicles of Narnia: The Lion, the Witch and the Wardrobe', 'author' => 'C.S. Lewis', 'language' => 'English', 'year' => 1950, 'copies_sold' => '85 million', 'genre' => 'Fantasy, Children\'s Literature', 'gist' => 'Four siblings discover a magical world through a wardrobe and join a talking lion to fight an evil witch.'],

        // Bengali Classics & Popular
        ['id' => 17, 'title' => 'Gitanjali', 'author' => 'Rabindranath Tagore', 'language' => 'Bengali', 'year' => 1910, 'copies_sold' => 'Unknown (Nobel Prize in Literature)', 'genre' => 'Poetry', 'gist' => 'A collection of devotional poems expressing spiritual love and the human connection to the divine.'],
        ['id' => 18, 'title' => 'Pather Panchali (Song of the Road)', 'author' => 'Bibhutibhushan Bandyopadhyay', 'language' => 'Bengali', 'year' => 1929, 'copies_sold' => 'Widely read, adapted into acclaimed film', 'genre' => 'Social Drama, Bildungsroman', 'gist' => 'The story of a young boy, Apu, growing up in a poor Brahmin family in rural Bengal.'],
        ['id' => 19, 'title' => 'Shesher Kabita (The Last Poem)', 'author' => 'Rabindranath Tagore', 'language' => 'Bengali', 'year' => 1929, 'copies_sold' => 'Highly influential', 'genre' => 'Novel, Romance', 'gist' => 'A poignant love story exploring themes of intellectual connection, societal norms, and the nature of love.'],
        ['id' => 20, 'title' => 'Chokher Bali (Sand in the Eye)', 'author' => 'Rabindranath Tagore', 'language' => 'Bengali', 'year' => 1903, 'copies_sold' => 'Classic Bengali novel', 'genre' => 'Psychological Drama, Social Novel', 'gist' => 'A complex tale of relationships, desire, and widowhood in colonial Bengal.'],
        ['id' => 21, 'title' => 'Aranyak (Of the Forest)', 'author' => 'Bibhutibhushan Bandyopadhyay', 'language' => 'Bengali', 'year' => 1939, 'copies_sold' => 'Celebrated work', 'genre' => 'Nature Writing, Philosophical Novel', 'gist' => 'A man takes a job as a forest manager in Bihar and reflects on nature, civilization, and humanity.'],
        ['id' => 22, 'title' => 'Feluda Samagra (Complete Feluda)', 'author' => 'Satyajit Ray', 'language' => 'Bengali', 'year' => '1965-1992 (series)', 'copies_sold' => 'Extremely popular detective series', 'genre' => 'Detective Fiction, Adventure', 'gist' => 'The adventures of Prodosh C. Mitter, a private investigator, solving mysteries across India.'],
        ['id' => 23, 'title' => 'Devdas', 'author' => 'Sarat Chandra Chattopadhyay', 'language' => 'Bengali', 'year' => 1917, 'copies_sold' => 'Widely adapted and read', 'genre' => 'Tragedy, Romance', 'gist' => 'A tragic love story of a wealthy young man who descends into alcoholism due to lost love and societal pressures.'],
        ['id' => 24, 'title' => 'Hajar Churashir Maa (Mother of 1084)', 'author' => 'Mahasweta Devi', 'language' => 'Bengali', 'year' => 1974, 'copies_sold' => 'Significant socio-political novel', 'genre' => 'Political Fiction, Social Commentary', 'gist' => 'A mother grapples with the death of her Naxalite son and the political turmoil of 1970s Bengal.'],
        ['id' => 25, 'title' => 'Aparajito (The Unvanquished)', 'author' => 'Bibhutibhushan Bandyopadhyay', 'language' => 'Bengali', 'year' => 1932, 'copies_sold' => 'Sequel to Pather Panchali', 'genre' => 'Social Drama, Bildungsroman', 'gist' => 'Continues the story of Apu as he moves to the city, pursues education, and faces life\'s challenges.'],
        ['id' => 26, 'title' => 'Professor Shonku (Complete Adventures)', 'author' => 'Satyajit Ray', 'language' => 'Bengali', 'year' => '1960s-1990s (series)', 'copies_sold' => 'Popular science fiction series', 'genre' => 'Science Fiction, Adventure', 'gist' => 'The diary entries of an eccentric Bengali inventor, Professor Shonku, detailing his fantastic inventions and global adventures.'],
        ['id' => 27, 'title' => 'Sonar Kella (The Golden Fortress)', 'author' => 'Satyajit Ray', 'language' => 'Bengali', 'year' => 1971, 'copies_sold' => 'Very popular Feluda novel', 'genre' => 'Detective Fiction, Adventure', 'gist' => 'Feluda investigates a case involving a young boy who claims to remember his past life in a golden fortress in Rajasthan.'],
        ['id' => 28, 'title' => 'Joy বাবা Felunath (The Elephant God)', 'author' => 'Satyajit Ray', 'language' => 'Bengali', 'year' => 1976, 'copies_sold' => 'Popular Feluda novel', 'genre' => 'Detective Fiction, Adventure', 'gist' => 'Feluda travels to Varanasi to solve a case of theft of a valuable Ganesh statue.'],
        ['id' => 29, 'title' => 'Chander Pahar (Mountain of the Moon)', 'author' => 'Bibhutibhushan Bandyopadhyay', 'language' => 'Bengali', 'year' => 1937, 'copies_sold' => 'Highly popular adventure novel', 'genre' => 'Adventure Novel', 'gist' => 'A young Bengali man seeks adventure in Africa, encountering dense forests, wild animals, and a legendary diamond mine.'],
        ['id' => 30, 'title' => 'Ichamati', 'author' => 'Bibhutibhushan Bandyopadhyay', 'language' => 'Bengali', 'year' => 1950, 'copies_sold' => 'Acclaimed novel', 'genre' => 'Social Drama, Historical', 'gist' => 'Depicts life in rural Bengal along the Ichamati river, focusing on the lives of indigo planters and local communities during British rule.'],
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AiServices $aiService, QdrantService $qdrantService)
    {
        parent::__construct();
        $this->aiService = $aiService;
        $this->qdrantService = $qdrantService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to populate Qdrant with book data...');

        $defaultCollectionName = Config::get('ai.qdrant.default_collection_name', 'my_documents');
        $collectionName = $this->argument('collection') ?: $this->ask('Enter the Qdrant collection name to use:', $defaultCollectionName);

        if (empty($collectionName)) {
            $this->error('Collection name cannot be empty.');

            return Command::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk-size');
        if ($chunkSize <= 0) {
            $this->error('Chunk size must be a positive integer.');

            return Command::FAILURE;
        }

        $bookChunks = array_chunk($this->books, $chunkSize);
        $totalBooks = count($this->books);
        $processedBooks = 0;

        foreach ($bookChunks as $chunkIndex => $bookChunk) {
            $this->info('Processing batch '.($chunkIndex + 1).' of '.count($bookChunks).'...');
            $pointsToUpsert = [];

            foreach ($bookChunk as $book) {
                $this->line("Processing: \"{$book['title']}\" by {$book['author']}");

                // Construct a comprehensive text string for embedding
                $embeddingTextParts = [];
                foreach ($book as $key => $value) {
                    if ($key !== 'id') { // Exclude 'id' from the embedding text
                        $embeddingTextParts[] = ucfirst($key).': '.$value;
                    }
                }
                $embeddingText = implode('. ', $embeddingTextParts).'.';

                $vector = $this->aiService->embed($embeddingText);
                if (! $vector) {
                    $this->warn("Could not generate embedding for \"{$book['title']}\". Skipping.");

                    continue;
                }

                // Ensure ID is unique and suitable for Qdrant (string or int)
                // Using a slug of title + author for more robust unique ID, or the predefined ID.
                $qdrantId = $book['id'] ?? Str::slug($book['title'].'-'.$book['author']);

                $pointsToUpsert[] = [
                    'id' => $qdrantId,
                    'vector' => $vector,
                    'payload' => [
                        'title' => $book['title'],
                        'author' => $book['author'],
                        'language' => $book['language'],
                        'year' => $book['year'],
                        'copies_sold' => $book['copies_sold'],
                        'genre' => $book['genre'],
                        'gist' => $book['gist'],
                        'searchable_content' => $embeddingText, // Store the text used for embedding
                        'source' => 'predefined_books_list', // Add a source for easier filtering later
                    ],
                ];
                $processedBooks++;
            }

            if (! empty($pointsToUpsert)) {
                try {
                    $this->qdrantService->upsertPoints($collectionName, $pointsToUpsert);
                    $this->info('Successfully upserted '.count($pointsToUpsert).' books in this batch.');
                } catch (\Exception $e) {
                    $this->error("Failed to upsert points to Qdrant collection '{$collectionName}': ".$e->getMessage());
                    // Optionally, decide if you want to stop or continue with next batch
                }
            } else {
                $this->info('No books to upsert in this batch (all might have been skipped).');
            }
            $this->info("Processed {$processedBooks} / {$totalBooks} books so far.");
        }

        $this->info("Finished populating Qdrant with book data. Total books processed: {$processedBooks}.");

        return Command::SUCCESS;
    }
}
