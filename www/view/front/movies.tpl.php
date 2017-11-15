<?php
if (!defined("_WOJO"))
    die('Direct access to this location is not allowed.');
?>
<div class="content-center"><img src="<?php echo UPLOADURL;?>/avatars/<?php echo (App::Auth()->avatar) ? App::Auth()->avatar : "blank.png";?>" alt="" class="avatar"></div>

<div class="content-center"><img class="subscribe-banner" src="/mani/view/front/images/movies_icon.png" alt="" class="header_icon"></div>

<?php if($this->data):?>
    <div style="">
        <table id="showListTableShows" class="tablesorter tablesorter-default tablesorter236e0314cb45fcolumnselector hasSaveSort hasFilters hasStickyHeaders" cellspacing="1" border="0" cellpadding="0" role="grid">
            <thead>
                <tr role="row" class="tablesorter-headerRow">
                    <th data-column="0" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Year</div></th>
                    <th data-column="1" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on style="user-select: none;"><div class="tablesorter-header-inner">Classification</div></th>
                    <th data-column="2" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Movie</div></th>
                    <th data-column="3" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Plot</div></th>
                    <th data-column="4" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" astyle="user-select: none;"><div class="tablesorter-header-inner">Studio</div></th>
                    <th data-column="5" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Rating</div></th>
                    <th data-column="5" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Subscribe</div></th>
                </tr>
                <tr role="row" class="tablesorter-filter-row tablesorter-ignoreRow hideme">
                    <td data-column="0"><input type="search" placeholder="" class="tablesorter-filter" data-column="0"></td>
                    <td data-column="1"><input type="search" placeholder="" class="tablesorter-filter" data-column="1"></td>
                    <td data-column="2"><input type="search" placeholder="" class="tablesorter-filter" data-column="2"></td>
                    <td data-column="3"><input type="search" placeholder="" class="tablesorter-filter" data-column="3"></td>
                    <td data-column="4"><input type="search" placeholder="" class="tablesorter-filter" data-column="4"></td>
                    <td data-column="5"><input type="search" placeholder="" class="tablesorter-filter" data-column="5"></td>
                    <td data-column="6"><input type="search" placeholder="" class="tablesorter-filter" data-column="6"></td>
                </tr>
            </thead>
            <tfoot class="hidden-print">
                <tr role="row">
                    <th data-column="1">&nbsp;</th>
                    <th data-column="1">&nbsp;</th>
                    <th data-column="2">&nbsp;</th>
                    <th data-column="3">&nbsp;</th>
                    <th data-column="4">&nbsp;</th>
                    <th data-column="5">&nbsp;</th>
                    <th data-column="6">&nbsp;</th>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach ($this->data["available"] as $locations):?>
                <?php foreach ($locations["content"] as $mrow):?>
                <tr role="row" class="odd">

                    <!-- Year -->
                    <td align="center">
                        <time datetime="2017-05-25T03:00:00+02:00" class="date"><?php echo $mrow["year"];?></time>
                    </td>

                    <!-- Classification -->
                    <td align="center" class="nowrap">
                        <?php echo $mrow["mpaa"];?>
                    </td>

                    <!-- Banner/Title -->
                    <td>
                        <div class="imgsmallposter small" style="text-align:center;">
                            <a href="http://dereferer.org/?http://www.imdb.com/title/<?php echo $mrow["imdbid"];?>" title="<?php echo $mrow["plot"];?>" style="width:100%;display:block;margin:0 auto;">
                                <img src="/mani/ajax/?do=image&loc=<?php echo $locations["title"];?>&type=<?php echo $mrow["type"];?>&media=<?php echo $mrow["media_poster"];?>" class="small" alt="<?php echo $mrow["title"];?>" title="<?php echo $mrow["plot"];?>">
                            </a>
                            <a href="http://dereferer.org/?http://www.imdb.com/title/<?php echo $mrow["imdbid"];?>" style="vertical-align:middle;display:block;" title="<?php echo $mrow["plot"];?>"><?php echo $mrow["title"];?></a>
                        </div>
                    </td>

                    <!-- Plot -->
                    <td align="center">
                        <span class="plot" title="<?php echo $mrow["plot"];?>" ><?php echo $mrow["plot"];?></span>
                    </td>

                    <!-- Studio -->
                    <td align="center">
                        <?php if (is_array($mrow["studio"]) || is_object($mrow["studio"])) { $count = 0; foreach ($mrow["studio"] as $studio) { if ($count > 4) { break; } ?>
                            <span style="display:block;"><?php echo $studio;?></span>
                            <?php $count ++; ?>
                        <?php } } else { ?>
                            <span style="display:block;"><?php echo $mrow["studio"];?></span>
                        <?php } ?>
                    </td>

                    <!-- Rating -->
                    <td align="center">
                        <?php echo $mrow["criticrating"];?>
                    </td>

                    <!-- Subscribe Toggle -->
                    <td align="center">
                        <input type="checkbox" name="media_enabled_<?php echo $mrow["id"];?>" id="media_enabled_<?php echo $mrow["id"];?>" <?php echo $mrow["enabled"];?> onclick="toggleShowSelection('<?php echo $mrow["id"];?>');">
                        <input type="hidden" name="media_type_<?php echo $mrow["id"];?>" id="media_type_<?php echo $mrow["id"];?>" value="<?php echo $mrow["type"];?>" >
                    </td>
                </tr>
                <?php endforeach;?>
            <?php endforeach;?>
            </tbody>
        </table>
    </div>
<?php endif;?>