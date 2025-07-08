const { useRef } = wp.element;
const { Button } = wp.components;
const { __ } = wp.i18n;

export default function RemoveButton({
  onClick,
  variant = "primary",
  isDestructive = true,
  disabled = false,
  size = "default",
  style = {},
  children,
}) {
  style = { width: "150px", justifyContent: "center", ...style };

  const alertDelay = useRef();
  function doubleClickAlert() {
    clearTimeout(alertDelay.current);
    alertDelay.current = setTimeout(
      () => alert(__("Double click to remove", "forms-bridge")),
      300
    );
  }

  return (
    <Button
      isDestructive={isDestructive}
      variant={variant}
      onClick={doubleClickAlert}
      onDoubleClick={(ev) => {
        onClick(ev);
        clearTimeout(alertDelay.current);
      }}
      style={style}
      showTooltip={true}
      label={__("Double click to remove", "forms-bridge")}
      disabled={disabled}
      size={size}
      __next40pxDefaultSize
    >
      {children}
    </Button>
  );
}
