<?php
namespace HUchatbots\Core;

/**
 * Registra todas as ações e filtros para o plugin.
 */
class Loader {

    /**
     * O array de ações registradas com WordPress.
     *
     * @var array
     */
    protected $actions;

    /**
     * O array de filtros registrados com WordPress.
     *
     * @var array
     */
    protected $filters;

    /**
     * Inicializa as coleções usadas para manter as ações e filtros.
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Adiciona uma nova ação ao array de ações registradas.
     *
     * @param string $hook          O nome da ação do WordPress à qual a $callback deve ser registrada.
     * @param object $component     Uma referência à instância do objeto no qual a $callback é definida.
     * @param string $callback      O nome da função definida no $component.
     * @param int    $priority      Opcional. A prioridade na qual a função deve ser disparada. Default is 10.
     * @param int    $accepted_args Opcional. O número de argumentos que devem ser passados para a $callback. Default é 1.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Adiciona um novo filtro ao array de filtros registrados.
     *
     * @param string $hook          O nome do filtro do WordPress ao qual a $callback deve ser registrada.
     * @param object $component     Uma referência à instância do objeto no qual a $callback é definida.
     * @param string $callback      O nome da função definida no $component.
     * @param int    $priority      Opcional. A prioridade na qual a função deve ser disparada. Default is 10.
     * @param int    $accepted_args Opcional. O número de argumentos que devem ser passados para a $callback. Default é 1.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Função auxiliar usada para registrar ações e hooks em uma coleção.
     *
     * @param array  $hooks         A coleção de hooks (seja $this->actions ou $this->filters).
     * @param string $hook          O nome da ação ou filtro do WordPress a ser registrado.
     * @param object $component     Uma referência à instância do objeto no qual a callback é definida.
     * @param string $callback      O nome da função definida no $component.
     * @param int    $priority      A prioridade na qual a função deve ser disparada.
     * @param int    $accepted_args O número de argumentos que devem ser passados para a $callback.
     * @return array                A coleção de ações e filtros registrados com WordPress.
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Registra os filtros e ações com WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }
    }

    /**
     * Remove uma ação registrada.
     *
     * @param string $hook     O nome da ação do WordPress a ser removida.
     * @param object $component A instância do objeto onde a ação foi definida.
     * @param string $callback  O nome da função que deveria ser chamada.
     * @param int    $priority  A prioridade da ação (padrão: 10).
     */
    public function remove_action($hook, $component, $callback, $priority = 10) {
        $this->remove($this->actions, $hook, $component, $callback, $priority);
    }

    /**
     * Remove um filtro registrado.
     *
     * @param string $hook     O nome do filtro do WordPress a ser removido.
     * @param object $component A instância do objeto onde o filtro foi definido.
     * @param string $callback  O nome da função que deveria ser chamada.
     * @param int    $priority  A prioridade do filtro (padrão: 10).
     */
    public function remove_filter($hook, $component, $callback, $priority = 10) {
        $this->remove($this->filters, $hook, $component, $callback, $priority);
    }

    /**
     * Função auxiliar para remover uma ação ou filtro.
     */
    private function remove(&$hooks, $hook, $component, $callback, $priority) {
        $hooks = array_filter($hooks, function($item) use ($hook, $component, $callback, $priority) {
            return !($item['hook'] === $hook &&
                     $item['component'] === $component &&
                     $item['callback'] === $callback &&
                     $item['priority'] === $priority);
        });
    }

    /**
     * Verifica se uma ação específica foi registrada.
     *
     * @param string $hook O nome da ação.
     * @return bool True se a ação foi registrada, false caso contrário.
     */
    public function has_action($hook) {
        return $this->has_hook($this->actions, $hook);
    }

    /**
     * Verifica se um filtro específico foi registrado.
     *
     * @param string $hook O nome do filtro.
     * @return bool True se o filtro foi registrado, false caso contrário.
     */
    public function has_filter($hook) {
        return $this->has_hook($this->filters, $hook);
    }

    /**
     * Função auxiliar para verificar se um hook existe.
     */
    private function has_hook($hooks, $hook) {
        foreach ($hooks as $item) {
            if ($item['hook'] === $hook) {
                return true;
            }
        }
        return false;
    }
}
