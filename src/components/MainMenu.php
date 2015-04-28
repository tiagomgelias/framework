<?php
use Selene\Matisse\AttributeType;
use Selene\Matisse\ComponentAttributes;
use Selene\Matisse\Components\Parameter;
use Selene\Matisse\VisualComponent;

class MainMenuAttributes extends ComponentAttributes
{
  /** @var  Parameter */
  public $header;
  /** @var  string */
  public $expand_icon;
  /** @var int */
  public $depth = 99;

  protected function typeof_header () { return AttributeType::SRC; }
  protected function typeof_expand_icon () { return AttributeType::TEXT; }
  protected function typeof_depth () { return AttributeType::NUM; }
}

class MainMenu extends VisualComponent
{
  protected $containerTag = 'ul';

  protected $depthClass = ['', 'nav-second-level', 'nav-third-level', 'nav-fourth-level'];

  /**
   * Returns the component's attributes.
   * @return MainMenuAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return MainMenuAttributes
   */
  public function newAttributes ()
  {
    return new MainMenuAttributes($this);
  }

  protected function render ()
  {
    global $application;
    $attr = $this->attrs ();

    $this->beginContent ();
    $this->runSet ($this->getChildren ('header'));
    $xi = $attr->get ('expand_icon');

    if (!empty($application->routingMap->routes))
      echo html (
        map ($application->routingMap->routes, function ($route) use ($xi) {
          if (!$route->onMenu) return null;
          $active = $route->selected ? '.active' : '';
          $sub    = $route->hasSubNav ? '.sub' : '';
          return [
            h ("li$active$sub", [
              h ("a$active", ['href' => $route->URL], [
                when ($route->icon, [h ('i.' . $route->icon), ' ']),
                either ($route->subtitle, $route->title),
                iftrue (isset($xi) && $route->hasSubNav, h ("span.$xi"))
              ]),
              when ($route->hasSubNav, $this->renderMenuItem ($route->routes, $xi))
            ])
          ];
        })
      );

    else echo html (
      map ($application->routingMap->groups, function ($grp) use ($xi) {
        return [
          h ('li.header', [
            h ('a', [
              when ($grp->icon, [h ('i.' . $grp->icon), ' ']),
              $grp->title
            ])
          ]),
          map ($grp->routes, function ($route) use ($xi) {
            if (!$route->onMenu) return null;
            $active = $route->selected ? '.active' : '';
            $sub    = $route->hasSubNav ? '.sub' : '';
            return [
              h ("li.treeview$active$sub", [
                h ("a$active", ['href' => $route->URL], [
                  when ($route->icon, [h ('i.' . $route->icon), ' ']),
                  either ($route->subtitle, $route->title),
                  iftrue (isset($xi) && $route->hasSubNav, h ("span.$xi"))
                ]),
                when ($route->hasSubNav, $this->renderMenuItem ($route->routes, $xi))
              ])
            ];
          })
        ];
      })
    );
  }

  private function renderMenuItem ($pages, $xi, $depth = 1)
  {
    if ($depth >= $this->attrs()->depth)
      return null;
    return h ('ul.nav.collapse.' . $this->depthClass[$depth], [
      map ($pages, function ($route) use ($xi, $depth) {
        if (!$route->onMenu) return null;
        $active  = $route->selected ? '.active' : '';
        $sub     = $route->hasSubNav ? '.sub' : '';
        $current = $route->matches ? '.current' : '';
        return
          h ("li.$active$sub$current", [
            h ("a$active", ['href' => $route->URL], [
              when ($route->icon, [h ('i.' . $route->icon), ' ']),
              either ($route->subtitle, $route->title),
              iftrue (isset($xi) && $route->hasSubNav, h ("span.$xi"))
            ]),
            when ($route->hasSubNav, $this->renderMenuItem ($route->routes, $xi, $depth + 1))
          ]);
      })
    ]);
  }

}

