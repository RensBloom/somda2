<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200501094837 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Put all rail-news feeds in the database';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('
            CREATE TABLE `somda_snf_spoor_nieuws_bron_feed` (
                `snf_id` BIGINT AUTO_INCREMENT NOT NULL,
                `snf_snb_id` BIGINT DEFAULT NULL,
                `snf_url` VARCHAR(255) NOT NULL,
                `snf_filter_results` TINYINT(1) DEFAULT \'0\' NOT NULL,
                INDEX `IDX_8A257AA6AD7A950` (`snf_snb_id`),
                PRIMARY KEY (`snf_id`)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE `somda_snf_spoor_nieuws_bron_feed` ADD CONSTRAINT `FK_8A257AA6AD7A950`
            FOREIGN KEY (`snf_snb_id`) REFERENCES `somda_snb_spoor_nieuws_bron` (`snb_id`)
        ');

        $this->addSql('
            INSERT INTO `somda_snf_spoor_nieuws_bron_feed` (`snf_snb_id`, `snf_url`, `snf_filter_results`) VALUES
            (18, \'https://nieuws.ns.nl/feed/nl\', \'0\'),
            (22, \'https://www.prorail.nl/nieuws/rss\', \'0\'),
            (39, \'https://www.treinreiziger.nl/rss/\', \'0\'),
            (51, \'https://www.treinenweb.nl/rss\', \'0\'),
            (38, \'https://www.treinennieuws.nl/feed/\', \'0\'),
            (13, \'https://feeds.ilent.nl/nieuws.rss\', \'1\'),
            (1, \'https://www.ad.nl/home/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/binnenland/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/buitenland/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/economie/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/politiek/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/bizar/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/rotterdam/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/den-haag/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/utrecht/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/amsterdam/rss.xml\', \'1\'),
            (1, \'https://www.ad.nl/wetenschap/rss.xml\', \'1\'),
            (41, \'https://www.volkskrant.nl/nieuws-achtergrond/rss.xml\', \'1\'),
            (41, \'https://www.volkskrant.nl/columns-opinie/rss.xml\', \'1\'),
            (41, \'https://www.volkskrant.nl/economie/rss.xml\', \'1\'),
            (41, \'https://www.volkskrant.nl/reizen/rss.xml\', \'1\'),
            (41, \'https://www.volkskrant.nl/wetenschap/rss.xml\', \'1\'),
            (52, \'https://www.telegraaf.nl/rss\', \'1\'),
            (52, \'https://www.telegraaf.nl/nieuws/binnenland/rss\', \'1\'),
            (7, \'https://www.dvhn.nl/?service=rssOwnArticlesStandard\', \'1\'),
            (7, \'https://www.dvhn.nl/groningen/?service=rss\', \'1\'),
            (7, \'https://www.dvhn.nl/drenthe/?service=rss\', \'1\'),
            (19, \'https://www.nu.nl/rss\', \'1\'),
            (19, \'https://www.nu.nl/rss/column\', \'1\'),
            (19, \'https://www.nu.nl/rss/economie\', \'1\'),
            (19, \'https://www.nu.nl/rss/internet\', \'1\'),
            (19, \'https://www.nu.nl/rss/opmerkelijk\', \'1\'),
            (19, \'https://www.nu.nl/rss/wetenschap\', \'1\'),
            (37, \'https://www.trouw.nl/voorpagina/rss.xml\', \'1\'),
            (37, \'https://www.trouw.nl/verdieping/rss.xml\', \'1\'),
            (37, \'https://www.trouw.nl/opinie/rss.xml\', \'1\'),
            (17, \'https://nieuws.nl/feed/\', \'1\'),
            (17, \'https://aalburg.nieuws.nl/feed/\', \'1\'),
            (17, \'https://aalsmeer.nieuws.nl/feed/\', \'1\'),
            (17, \'https://aalten.nieuws.nl/feed/\', \'1\'),
            (17, \'https://alblasserdam.nieuws.nl/feed/\', \'1\'),
            (17, \'https://alkmaar.nieuws.nl/feed/\', \'1\'),
            (17, \'https://almelo.nieuws.nl/feed/\', \'1\'),
            (17, \'https://almere.nieuws.nl/feed/\', \'1\'),
            (17, \'https://alphen-aan-den-rijn.nieuws.nl/feed/\', \'1\'),
            (17, \'https://amersfoort.nieuws.nl/feed/\', \'1\'),
            (17, \'https://amstelveen.nieuws.nl/feed/\', \'1\'),
            (17, \'https://amsterdam.nieuws.nl/feed/\', \'1\'),
            (17, \'https://apeldoorn.nieuws.nl/feed/\', \'1\'),
            (17, \'https://arnhem.nieuws.nl/feed/\', \'1\'),
            (17, \'https://baarn.nieuws.nl/feed/\', \'1\'),
            (17, \'https://barendrecht.nieuws.nl/feed/\', \'1\'),
            (17, \'https://barneveld.nieuws.nl/feed/\', \'1\'),
            (17, \'https://bedum.nieuws.nl/feed/\', \'1\'),
            (17, \'https://bergen-op-zoom.nieuws.nl/feed/\', \'1\'),
            (17, \'https://breda.nieuws.nl/feed/\', \'1\'),
            (17, \'https://culemborg.nieuws.nl/feed/\', \'1\'),
            (17, \'https://den-bosch.nieuws.nl/feed/\', \'1\'),
            (17, \'https://den-haag.nieuws.nl/feed/\', \'1\'),
            (17, \'https://deventer.nieuws.nl/feed/\', \'1\'),
            (17, \'https://dordrecht.nieuws.nl/feed/\', \'1\'),
            (17, \'https://druten.nieuws.nl/feed/\', \'1\'),
            (17, \'https://ede.nieuws.nl/feed/\', \'1\'),
            (17, \'https://eindhoven.nieuws.nl/feed/\', \'1\'),
            (17, \'https://enkhuizen.nieuws.nl/feed/\', \'1\'),
            (17, \'https://enschede.nieuws.nl/feed/\', \'1\'),
            (17, \'https://groesbeek.nieuws.nl/feed/\', \'1\'),
            (17, \'https://groningen.nieuws.nl/feed/\', \'1\'),
            (17, \'https://haarlem.nieuws.nl/feed/\', \'1\'),
            (17, \'https://heerenveen.nieuws.nl/feed/\', \'1\'),
            (17, \'https://heerlen.nieuws.nl/feed/\', \'1\'),
            (17, \'https://hengelo.nieuws.nl/feed/\', \'1\'),
            (17, \'https://hoogeveen.nieuws.nl/feed/\', \'1\'),
            (17, \'https://hoorn.nieuws.nl/feed/\', \'1\'),
            (17, \'https://houten.nieuws.nl/feed/\', \'1\'),
            (17, \'https://leeuwarden.nieuws.nl/feed/\', \'1\'),
            (17, \'https://leiden.nieuws.nl/feed/\', \'1\'),
            (17, \'https://maastricht.nieuws.nl/feed/\', \'1\'),
            (17, \'https://meppel.nieuws.nl/feed/\', \'1\'),
            (17, \'https://nijmegen.nieuws.nl/feed/\', \'1\'),
            (17, \'https://raalte.nieuws.nl/feed/\', \'1\'),
            (17, \'https://rijnwaarden.nieuws.nl/feed/\', \'1\'),
            (17, \'https://roosendaal.nieuws.nl/feed/\', \'1\'),
            (17, \'https://rotterdam.nieuws.nl/feed/\', \'1\'),
            (17, \'https://schiedam.nieuws.nl/feed/\', \'1\'),
            (17, \'https://tilburg.nieuws.nl/feed/\', \'1\'),
            (17, \'https://utrecht.nieuws.nl/feed/\', \'1\'),
            (17, \'https://vlissingen.nieuws.nl/feed/\', \'1\'),
            (17, \'https://zwolle.nieuws.nl/feed/\', \'1\'),
            (10, \'https://www.gelderlander.nl/home/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/s-hertogenbosch/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/achterhoek/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/arnhem-e-o/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/betuwe/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/de-vallei/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/eindhoven/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/enschede/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/liemers/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/maas-en-waal/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/maasland/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/nijmegen-e-o/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/oss-uden-e-o/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/rivierenland/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/utrecht/rss.xml\', \'1\'),
            (10, \'https://www.gelderlander.nl/zutphen/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/home/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/bergen-op-zoom/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/brabant/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/breda/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/dordrecht/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/etten-leur/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/hoeksche-waard/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/moerdijk/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/oosterhout/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/rivierenland/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/roosendaal/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/tholen/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/tilburg/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/waalwijk-heusden/rss.xml\', \'1\'),
            (5, \'https://www.bndestem.nl/zeeland/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/home/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/achterhoek/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/almelo/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/amersfoort/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/apeldoorn/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/arnhem/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/deventer/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/enschede/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/flevoland/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/hengelo/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/kampen/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/kop-van-overijssel/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/nijmegen/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/reggestreek/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/salland/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/vechtdal/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/veluwe/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/zutphen/rss.xml\', \'1\'),
            (34, \'https://www.destentor.nl/zwolle/rss.xml\', \'1\'),
            (23, \'https://www.pzc.nl/home/rss.xml\', \'1\'),
            (23, \'https://www.pzc.nl/bergen-op-zoom/rss.xml\', \'1\'),
            (23, \'https://www.pzc.nl/breda/rss.xml\', \'1\'),
            (23, \'https://www.pzc.nl/dordrecht/rss.xml\', \'1\'),
            (23, \'https://www.pzc.nl/roosendaal/rss.xml\', \'1\'),
            (23, \'https://www.pzc.nl/rotterdam/rss.xml\', \'1\'),
            (27, \'https://www.rtvdrenthe.nl/rss/nieuws\', \'1\'),
            (28, \'https://nieuws.rtvkatwijk.nl/feed/\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/alkmaar\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/amsterdam\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/nhgooi\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/haarlem\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/haarlemmermeer\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/noordkop\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/schiphol\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/west-friesland\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/ijmond\', \'1\'),
            (29, \'https://rss.nhnieuws.nl/rss/zaanstreek-waterland\', \'1\'),
            (31, \'https://xml.rtvoost.nl/rss/\', \'1\'),
            (32, \'https://www.rijnmond.nl/rss\', \'1\'),
            (33, \'https://www.rtvutrecht.nl/rss/index.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/all/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/economie/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/digitaal/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/wetenschap/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/drenthe/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/flevoland/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/friesland/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/gelderland/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/groningen/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/limburg/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/noord-brabant/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/noord-holland/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/overijssel/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/utrecht/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/zeeland/rss.xml\', \'1\'),
            (4, \'https://www.blikopnieuws.nl/feeds/zuid-holland/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/home/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/bommelerwaard/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/brabant/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/breda/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/den-bosch-vught/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/eindhoven/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/meierij/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/oss-e-o/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/tilburg-e-o/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/uden-veghel-e-o/rss.xml\', \'1\'),
            (6, \'https://www.bd.nl/waalwijk-heusden-e-o/rss.xml\', \'1\'),
            (20, \'https://www.parool.nl/nederland/rss.xml\', \'1\'),
            (20, \'https://www.parool.nl/columns-opinie/rss.xml\', \'1\'),
            (24, \'https://www.rd.nl/laatste-nieuws-7.4514?ot=rd.rss.ot\', \'1\'),
            (24, \'https://www.rd.nl/binnenland-7.4508?ot=rd.rss.ot\', \'1\'),
            (24, \'https://www.rd.nl/economie-7.4511?ot=rd.rss.ot\', \'1\'),
            (24, \'https://www.rd.nl/politiek-7.4518?ot=rd.rss.ot\', \'1\'),
            (15, \'https://www.limburger.nl/rss/section/d0519dbc-b486-4ea9-b732-a4d500db5cf1\', \'1\'),
            (8, \'https://www.ed.nl/home/rss.xml\', \'1\'),
            (8, \'https://www.ed.nl/brabant/rss.xml\', \'1\'),
            (8, \'https://www.ed.nl/den-bosch/rss.xml\', \'1\'),
            (8, \'https://www.ed.nl/eindhoven/rss.xml\', \'1\'),
            (8, \'https://www.ed.nl/helmond/rss.xml\', \'1\'),
            (8, \'https://www.ed.nl/tilburg/rss.xml\', \'1\'),
            (8, \'https://www.ed.nl/veldhoven/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/home/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/achterhoek/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/almelo-e-o/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/apeldoorn/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/deventer/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/enschede-e-o/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/hengelo-e-o/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/oldenzaal-e-o/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/reggestreek/rss.xml\', \'1\'),
            (35, \'https://www.tubantia.nl/zwolle/rss.xml\', \'1\'),
            (43, \'https://www.businessinsider.nl/feed/\', \'1\'),
            (3, \'https://rss.at5.nl/rss\', \'1\'),
            (3, \'https://rss.at5.nl/rss/achtergrond\', \'1\'),
            (3, \'https://rss.at5.nl/rss/politiek\', \'1\'),
            (46, \'https://www.lc.nl/?service=rss\', \'1\'),
            (49, \'https://www.gic.nl/startpagina/rss\', \'1\')
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // Not applicable
    }
}
