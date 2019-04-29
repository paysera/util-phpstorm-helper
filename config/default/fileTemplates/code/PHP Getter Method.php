#if(${RETURN_TYPE} == ${TYPE_HINT})#else/**
 * @return ${TYPE_HINT}
 */#end
public ${STATIC} function ${GET_OR_IS}${NAME}()#if(${RETURN_TYPE} == ${TYPE_HINT}): ${RETURN_TYPE}#else#end
{
#if (${STATIC} == "static")
    return self::$${FIELD_NAME};
#else
    return $this->${FIELD_NAME};
#end
}
