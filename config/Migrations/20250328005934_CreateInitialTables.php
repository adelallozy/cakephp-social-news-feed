<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateInitialTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {

        // the first version of users table that has been planned to be modified ...
        $this->getAdapter()->execute("
            DROP VIEW IF EXISTS `social_users` ; 
            CREATE VIEW social_users AS
            SELECT 
                id,
                alias AS username
            FROM users;
        ") ;
 
        $this->getAdapter()->execute("
            DROP TABLE IF EXISTS `social_posts`;   
            CREATE TABLE social_posts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT  NOT NULL,    
                slug VARCHAR(255) UNIQUE,
                visibility VARCHAR(20) DEFAULT 'public', -- public, private, unlisted, draft
                seo_title VARCHAR(255),
                seo_description TEXT,
                seo_image VARCHAR(255), -- link to main image for OG/SEO
                published_at DATETIME DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME DEFAULT NULL
            );
        ") ;

        $this->getAdapter()->execute("
            DROP TRIGGER IF EXISTS  `before_insert_social_posts` ;   
            CREATE TRIGGER before_insert_social_posts
            BEFORE INSERT ON social_posts
            FOR EACH ROW
            BEGIN
                IF NEW.id IS NULL OR NEW.id <= 0 THEN
                    SET NEW.id = nextval(ids_seq); 
                END IF;
            END  
        ") ;

         
        $this->getAdapter()->execute(" 
            DROP TABLE IF EXISTS `social_post_blocks`;  
            CREATE TABLE social_post_blocks (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                post_id BIGINT NOT NULL, 
                parent_id BIGINT NOT NULL DEFAULT 0  , 
                type VARCHAR(50) NOT NULL  DEFAULT 'paragraph'  ,                -- e.g., paragraph, image, heading
                content JSON NOT NULL,                   -- dynamic structured content
                style JSON,                        
                position INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
        ") ;

        $this->getAdapter()->execute("
            DROP TRIGGER IF EXISTS  `before_insert_social_post_blocks` ;   
            CREATE TRIGGER before_insert_social_post_blocks
            BEFORE INSERT ON social_post_blocks
            FOR EACH ROW
            BEGIN
                IF NEW.id IS NULL OR NEW.id <= 0 THEN
                    SET NEW.id = nextval(ids_seq); 
                END IF;
            END  
        ") ; 
    }


    public function down():void{
        $this->getAdapter()->execute(' 
            DROP TRIGGER IF EXISTS  `before_insert_social_posts` ;  
            DROP TRIGGER IF EXISTS  `before_insert_social_post_blocks` ;    
        ') ;
 
        $this->getAdapter()->execute(' 

            DROP VIEW IF EXISTS `social_users` ; 
            DROP TABLE IF EXISTS `social_posts`;  
            DROP TABLE IF EXISTS `social_post_blocks`;     
        ') ;
    }
}
