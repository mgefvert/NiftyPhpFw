@echo off

call phpunit NiftyTestSuite
if errorlevel 1 pause
