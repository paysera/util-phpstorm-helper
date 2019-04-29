#if (${SCALAR_TYPE_HINT} == ${TYPE_HINT})#else/**
 * @param ${TYPE_HINT} $${PARAM_NAME}
 * @return $this
 */#end
public function set${NAME}(#if (${SCALAR_TYPE_HINT} == ${TYPE_HINT})${SCALAR_TYPE_HINT} #else#end$${PARAM_NAME}): self
{
    $this->${FIELD_NAME} = $${PARAM_NAME};
    return $this;
}
