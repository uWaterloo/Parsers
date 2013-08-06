<?php


function parse_prerequisites($reqs)
{
  return array();
}


print_r(parse_prerequisites('Prereq: AMATH 242/341/CM 271/CS 371 or CS 370'));
print_r(parse_prerequisites('Prereq: PHYS 334 or AMATH 373; PHYS 364 or AMATH 351; PHYS 365 or (AMATH 332 and 353)'));

?>
