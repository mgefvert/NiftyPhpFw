@echo off

for %%f in (examples\blog) do (

    echo Updating %%f...

    rem Remove old directories
    if exist %%f\sys   rd /s /q %%f\sys
    if exist %%f\tools rd /s /q %%f\tools

    rem Copy sys and tools folders
    xcopy /e /i /q source\sys %%f\sys
    xcopy /e /i /q source\tools %%f\tools
    
    echo.
)

